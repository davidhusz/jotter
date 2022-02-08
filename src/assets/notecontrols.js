"use strict";

// Global variables
var noteList, notificationTimeout, lastFetch;

window.addEventListener("DOMContentLoaded", () => {
  updateNoteList();
  lastFetch = Date.now();
});

window.addEventListener("focus", () => {
  let now = Date.now();
  // If it has been more than one second since we last fetched the latest notes
  if ((now - lastFetch) > 1000) {
    fetchLatestNotes();
    lastFetch = now;
  }
});

function updateNoteList() {
  noteList = [];
  for (let noteElement of document.querySelectorAll(".note")) {
    let note = new Note(noteElement);
    noteList.push(note);
    setNoteControls(note);
  }
}

function prependToNoteList(noteHTML) {
    let noteListContainer = document.querySelector(".note-list");
    let newNoteContainer = document.createElement("div");
    noteListContainer.insertBefore(newNoteContainer, noteListContainer.firstChild);
    newNoteContainer.outerHTML = noteHTML;
    updateNoteList();
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
      } else if (controlType == "trash") {
        removeNote(note);
      }
    };
  }
}

function fetchLatestNotes() {
  let lastSeenNote = noteList.find(
    note => !note.container.classList.contains("removed")
  );
  sendBackendRequest(`${location.pathname}?before=${lastSeenNote.id}`, false, data => {
    if (data !== "") {
      prependToNoteList(data);
      showNotification("Fetched latest notes");
    }
  });
}

function Note(container) {
  this.container = container;
  this.id = container.id.substring(1);  // removes the 'N' id prefix
  this.type = container.classList[1];
}

function sendBackendRequest(endpoint, data, onSuccess) {
  fetch(endpoint, {
    method: data ? "POST" : "GET",
    ...(data ? {body: data} : {}),
    headers: {
      "Accept": "text/html;fragment=true",
      "Content-Type": "application/x-www-form-urlencoded"
    }
  }).then(
    response => {
      if (response.ok) {
        return response.text();
      } else {
        throw response;
      }
    },
    logError
  ).then(
    onSuccess,
    logError
  );
}

function logError(error) {
  console.error(error);
  // showNotification("An error occurred. Check console for details.");
  showNotification("Error. Check console");
}

function copyNoteToClipboard(note) {
  let noteFilepath = `/note/${note.id}/raw`;
  sendBackendRequest(noteFilepath, false, contents => {
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
  });
}

function bumpNote(note) {
  sendBackendRequest("/bump", `id=${note.id}`, data => {
    note.container.remove();
    prependToNoteList(data);
    showNotification("Note bumped.");
  });
}

function removeNote(note) {
  sendBackendRequest("/trash", `id=${note.id}`, data => {
    note.container.classList.add("removed");
    showNotification("Note moved to trash. <span>Undo</span>", 7);
    document.querySelector(".notification span").addEventListener("click", () => {
      restoreNote(note);
    });
  });
}

function restoreNote(note) {
  sendBackendRequest("/restore", `id=${note.id}`, data => {
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
