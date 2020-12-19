"use strict";

// Global variables
var noteList, notificationTimeout;

window.addEventListener("DOMContentLoaded", () => {
  noteList = [];
  for (let noteElement of document.querySelectorAll(".note")) {
    noteList.push(new Note(noteElement));
  }
  
  for (let note of noteList) {
    let controls = note.container.querySelectorAll(".controls > *");
    for (let control of controls) {
      let controlType = control.classList[0];
      control.addEventListener("click", () => {
        if (controlType == "copy") {
          copyNoteToClipboard(note);
        } else if (controlType == "delete") {
          removeNote(note);
        }
      });
    }
  }
});

window.addEventListener("keydown", function(event) {
  switch (event.key) {
    case "ArrowDown":
    case "f":
      event.preventDefault();
      if (!isAnyNoteSelected()) {
        selectNote(noteList[0]);
      } else {
        let currentlySelected = noteList.indexOf(getSelectedNote());
        if (currentlySelected < noteList.length-1) {
          selectNote(noteList[currentlySelected+1]);
        } else {
          unselectAllNotes();
        }
      }
      break;
    
    case "ArrowUp":
    case "w":
      if (isAnyNoteSelected()) {
        event.preventDefault();
        let currentlySelected = noteList.indexOf(getSelectedNote());
        if (currentlySelected > 0) {
          selectNote(noteList[currentlySelected-1]);
        } else {
          unselectAllNotes();
        }
      }
      break;
    
    case "Escape":
      unselectAllNotes();
      break;
    
    case "c":
      if (isAnyNoteSelected()) {
        copyNoteToClipboard(getSelectedNote());
      }
      break;
    
    case "d":
      if (isAnyNoteSelected()) {
        getSelectedNote().container.querySelector(".download").click();
      }
      break;
    
    case "t": // "t" for trash
      if (isAnyNoteSelected()) {
        removeNote(getSelectedNote());
      }
      break;
  }
});

function Note(container) {
  this.container = container;
  this.type = container.classList[1];
  this.filepath = container.dataset.filepath;
  this.filename = this.filepath.split("/").pop();
}

function selectNote(note) {
  unselectAllNotes();
  note.container.classList.add("selected");
  // note.container.scrollIntoView();
}

function unselectNote(note) {
  note.container.classList.remove("selected");
}

function isNoteSelected(note) {
  return note.container.classList.contains("selected");
}

function isAnyNoteSelected() {
  return noteList.some(note => isNoteSelected(note));
}

function getSelectedNote() {
  return noteList.filter(note => isNoteSelected(note))[0];
}

function unselectAllNotes() {
  if (isAnyNoteSelected()) {
    unselectNote(getSelectedNote());
  }
}

function performBackendOperation(note, operation, onSuccess) {
  let request = new Request("api/" + operation + ".php", {
    method: "POST",
    body: "file=" + note.filename,
    headers: {
      "Content-Type": "application/x-www-form-urlencoded"
    }
  });
  console.log("Sending request: ", request);
  fetch(request).then(
    (response) => {
      if (!response.ok) {
        throw Error("Server returned: " + response.status + " " + response.statusText);
      }
    },
    (error) => {
      throw Error("Could not send request. " + error);
    }
  ).then(
    onSuccess,
    logError
  );
}

function logError(error) {
  console.error(error);
  showNotification("Error. Check console");
}

function copyNoteToClipboard(note) {
  fetch(note.filepath).then(
    (response) => {
      if (response.ok) {
        return response.text();
      } else {
        throw Error("Server returned: " + response.status + " " + response.statusText);
      }
    }
  ).then(
    (contents) => {
      contents = contents.trimEnd();
      // TODO: account for different kinds of MIME types
      if (navigator.clipboard !== undefined) {
        // this only works with https apparently
        // also, it has to be triggered via a user event
        try {
          let contentsForClipboard = [new ClipboardItem({"text/plain": contents})];
          navigator.clipboard.write(contentsForClipboard).then(
            () => { showNotification("Note copied.") },
            logError
          )
        } catch (error) {
          if (note.type == "text") {
            navigator.clipboard.writeText(contents).then(
              () => { showNotification("Note copied.") },
              logError
            );
          } else {
            // copy URL with GET parameters excluded
            navigator.clipboard.writeText(
              location.href.replace(location.search, "") + note.filepath
            ).then(
              () => { showNotification("URL copied.") },
              logError
            );
          }
        }
      } else {
        // fallback using prompt pop-up
        prompt("Clipboard cannot be accessed, please copy manually", contents);
      }
    },
    logError
  )
}

function removeNote(note) {
  performBackendOperation(note, "delete", (response) => {
    showNotification("Note deleted. <span>Undo</span>", 7);
    note.container.classList.add("removed");
    document.querySelector(".notification span").addEventListener("click", () => {
      restoreNote(note);
    });
  });
}

function restoreNote(note) {
  performBackendOperation(note, "restore", (response) => {
    showNotification("Note restored.");
    note.container.classList.remove("removed");
  });
}

function showNotification(message, seconds = 3) {
  let notificationBar = document.querySelector(".notification");
  notificationBar.innerHTML = message;
  clearTimeout(notificationTimeout);
  notificationBar.classList.add("active");
  notificationTimeout = setTimeout(() => notificationBar.classList.remove("active"), seconds * 1000);
}