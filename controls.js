"use strict";

window.addEventListener("DOMContentLoaded", function() {
  let controlButtons = document.querySelectorAll(".controls > *");
  
  for (let button of controlButtons) {
    button.onclick = function() {
      let buttonType = button.classList[0];
      let note = {
        container: button.parentNode.parentNode,
//        filepath: button.parentNode.parentNode.querySelector(".download").getAttribute("href"),
        filepath: button.parentNode.parentNode.dataset.filepath,
        filename: button.parentNode.parentNode.dataset.filepath.split("/").pop(),
        type: button.parentNode.parentNode.classList[1],
        notification: {
          success: button.dataset.notificationOnSuccess,
          failure: button.dataset.notificationOnFailure,
          restore: button.dataset.notificationOnRestore
        }
      }
      
      switch (buttonType) {
        case "copy":
          copyNoteToClipboard(note);
          break;
        case "delete":
          removeNote(note);
          break;
      }
    }
  }
});

var notificationTimeout;

function copyNoteToClipboard(note) {
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
        showNotification(note.notification.success);
      } else {
        prompt("Clipboard cannot be accessed, please copy manually", noteContent);
      }
    });
}

function removeNote(note) {
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
            showNotification(note.notification.success, 7);
            document.querySelector(".notification span").onclick = function() {
              restoreNote(note);
            }
          })
    .catch(function(error) {
             showNotification(note.notification.failure);
           });
}

function restoreNote(note) {
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
            showNotification(note.notification.restore);
          })
    .catch(function(error) {
             showNotification(note.notification.failure);
           });
}

function showNotification(message, seconds = 3) {
  let notificationBar = document.querySelector(".notification");
  notificationBar.innerHTML = message;
  clearTimeout(notificationTimeout);
  notificationBar.classList.add("active");
  notificationTimeout = setTimeout(() => notificationBar.classList.remove("active"), seconds * 1000);
}
