"use strict";

window.addEventListener("keydown", function(event) {
  if (event.key == "n" && !getNoteForm().classList.contains("active")) {
    event.preventDefault();
    showNoteForm();
  } else if (event.key == "Escape") {
    hideNoteForm();
  }
});

function getNoteForm() {
  return document.querySelector(".new-note");
}

function showNoteForm() {
  let formContainer = getNoteForm();
  let form = formContainer.querySelector("form");
  let contentBox = formContainer.querySelector("textarea");
  formContainer.classList.add("active");
  
  contentBox.focus();
  contentBox.addEventListener("keydown", function(event) {
    if (event.ctrlKey && event.key == "Enter") {
      submitNoteForm();
    }
  });
  
  form.addEventListener("submit", function(event) {
      event.preventDefault();
      submitNoteForm();
  });
  
  window.addEventListener("click", function(event) {
    let modal = getNoteForm().querySelector(".modal");
    if (!modal.contains(event.target)
        // HACK: ideally the event listener should be removed once the modal is closed
        && event.target !== document.querySelector(".instructions span.clickable")) {
      hideNoteForm();
    }
  });
}

function hideNoteForm() {
  let formContainer = getNoteForm();
  formContainer.classList.remove("active");
}

function submitNoteForm() {
  let contentBox = getNoteForm().querySelector("textarea");
  let noteContent = contentBox.value;
  fetch("api/post.php", {
    method: "POST",
    body: "note=" + encodeURIComponent(noteContent),
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
            contentBox.value = "";
            console.log("Note saved.");
            window.location.reload();
          })
    .catch(function(error) {
             console.error("Error saving note. Check request history.");
           })
}
