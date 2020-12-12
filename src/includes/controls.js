"use strict";

window.addEventListener("DOMContentLoaded", function() {
  let controlButtons = document.querySelectorAll(".controls > *");
  
  for (let button of controlButtons) {
    button.onclick = function() {
      let buttonType = button.classList[0];
      let noteElement = button.parentNode.parentNode;
      let notifications = {
        success: button.dataset.notificationOnSuccess,
        failure: button.dataset.notificationOnFailure,
        restore: button.dataset.notificationOnRestore
      }
      
      switch (buttonType) {
        case "copy":
          copyNoteToClipboard(noteElement, notifications);
          break;
        case "delete":
          removeNote(noteElement, notifications);
          break;
      }
    }
  }
});

var notificationTimeout;

function getNoteDetails(noteElement) {
  let noteDetails = {
    container: noteElement,
    filepath: noteElement.dataset.filepath,
    filename: noteElement.dataset.filepath.split("/").pop(),
    type: noteElement.classList[1]
  }
  return noteDetails;
}

function copyNoteToClipboard(noteElement, notifications) {
  let note = getNoteDetails(noteElement);
  fetch(note.filepath)
    .then(response => response.text())
    .then(function(noteContent) {
      noteContent = noteContent.trimEnd();
      if (navigator.clipboard !== undefined) {
        // this only works with https apparently
        try {
          // TODO: account for different kinds of mime types
          let noteContentForClipboard = [new ClipboardItem({"text/plain": noteContent})];
          navigator.clipboard.write(noteContentForClipboard);
//                    .then(() => console.log("Successfully copied to clipboard"),
//                          () => console.error("Copying to clipboard failed"));
        } catch (error) {
          if (note.type == "text") {
            navigator.clipboard.writeText(noteContent);
//                      .then(() => console.log("Successfully copied to clipboard"),
//                            () => console.error("Copying to clipboard failed"));
          } else {
            // make sure GET parameters are excluded
            navigator.clipboard.writeText(location.href.replace(location.search, "") + note.filepath);
//                      .then(() => console.log("Successfully copied to clipboard"),
//                            () => console.error("Copying to clipboard failed"));
          }
        }
        showNotification(notifications.success);
      } else {
        prompt("Clipboard cannot be accessed, please copy manually", noteContent);
      }
    });
}

function removeNote(noteElement, notifications) {
  let note = getNoteDetails(noteElement);
  fetch("api/delete.php", {
    method: "POST",
    body: "file=" + note.filename,
    headers: {
      "Content-Type": "application/x-www-form-urlencoded"
    }
  }).then(function(response) {
            if (response.ok) {
              return response.text();
            } else {
              throw Error(response.statusText);
            }
          })
    .then(function(message) {
            note.container.classList.add("removed");
            showNotification(notifications.success, 7);
            document.querySelector(".notification span").onclick = function() {
              restoreNote(noteElement, notifications);
            }
          })
    .catch(function(error) {
             showNotification(notifications.failure);
           });
}

function restoreNote(noteElement, notifications) {
  let note = getNoteDetails(noteElement);
  fetch("api/restore.php", {
    method: "POST",
    body: "file=" + note.filename,
    headers: {
      "Content-Type": "application/x-www-form-urlencoded"
    }
  }).then(function(response) {
            if (response.ok) {
              return response.text();
            } else {
              throw Error(response.statusText);
            }
          })
    .then(function(message) {
            note.container.classList.remove("removed");
            showNotification(notifications.restore);
          })
    .catch(function(error) {
             showNotification(notifications.failure);
           });
}

function showNotification(message, seconds = 3) {
  let notificationBar = document.querySelector(".notification");
  notificationBar.innerHTML = message;
  clearTimeout(notificationTimeout);
  notificationBar.classList.add("active");
  notificationTimeout = setTimeout(() => notificationBar.classList.remove("active"), seconds * 1000);
}
