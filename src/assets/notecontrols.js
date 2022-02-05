"use strict";

// Global variables
var noteList, queryString, queryParams, notificationTimeout;

window.addEventListener("DOMContentLoaded", updateNoteList);

function updateNoteList() {
  noteList = [];
  for (let noteElement of document.querySelectorAll(".note")) {
    let note = new Note(noteElement);
    noteList.push(note);
    setNoteControls(note);
  }
}

function setNoteControls(note) {
  let controls = note.container.querySelectorAll(".controls > *");
  for (let control of controls) {
    let controlType = control.classList[0];
    control.onclick = () => {
      if (controlType == "copy") {
        copyNoteToClipboard(note);
      } else if (controlType == "bump") {
        bumpNote(note);
      } else if (controlType == "delete") {
        removeNote(note);
      }
    };
  }
}

function Note(container) {
  this.container = container;
  this.id = container.id.substring(1);  // removes the 'N' id prefix
  this.type = container.classList[1];
}

function performBackendOperation(note, operation, onSuccess) {
  let request = new Request("/" + operation, {
    method: "POST",
    body: "id=" + note.id,
    headers: {
      "Accept": "text/html;fragment=true",
      "Content-Type": "application/x-www-form-urlencoded"
    }
  });
  console.log("Sending request: ", request);
  fetch(request).then(
    (response) => {
      if (response.ok) {
        return response.text();
      } else {
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
  let noteFilepath = `/note/${note.id}/raw`;
  fetch(noteFilepath).then(
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
            navigator.clipboard.writeText(
              location.origin + noteFilepath
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

function bumpNote(note) {
  performBackendOperation(note, "bump", (data) => {
    note.container.remove();
    let noteListContainer = document.querySelector(".note-list");
    let newNoteContainer = document.createElement("div");
    noteListContainer.insertBefore(newNoteContainer, noteListContainer.firstChild);
    newNoteContainer.outerHTML = data;
    updateNoteList();
    showNotification("Note bumped.");
  });
}

function removeNote(note) {
  performBackendOperation(note, "trash", (data) => {
    showNotification("Note deleted. <span>Undo</span>", 7);
    note.container.classList.add("removed");
    document.querySelector(".notification span").addEventListener("click", () => {
      restoreNote(note);
    });
  });
}

function restoreNote(note) {
  performBackendOperation(note, "restore", (data) => {
    note.container.outerHTML = data;
    note.container.classList.remove("removed");
    updateNoteList();
    showNotification("Note restored.");
  });
}

function showNotification(message, seconds = 3) {
  let notificationBar = document.querySelector(".notification");
  notificationBar.innerHTML = message;
  clearTimeout(notificationTimeout);
  notificationBar.classList.add("active");
  notificationTimeout = setTimeout(() => notificationBar.classList.remove("active"), seconds * 1000);
}
