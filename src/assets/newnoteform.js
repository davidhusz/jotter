"use strict";

var formContainer, form, contentBox;

window.addEventListener("DOMContentLoaded", function() {
  formContainer = document.querySelector(".new-note");
  form = formContainer.querySelector("form");
  contentBox = formContainer.querySelector("textarea");
  
  document.querySelector(".note-form-button").onclick = showNoteForm;
  document.querySelector(".close-button").onclick = hideNoteForm;
  
  form.addEventListener("submit", function(event) {
    event.preventDefault();
    submitNoteForm();
  });
  
  contentBox.addEventListener("keydown", function(event) {
    if (event.ctrlKey && event.key == "Enter") {
      submitNoteForm();
    }
  });
});

window.addEventListener("keydown", function(event) {
  if (event.key == "n" && !noteFormIsShown()) {
    event.preventDefault();
    showNoteForm();
  } else if (event.key == "Escape") {
    hideNoteForm();
  }
});

function showNoteForm() {
  formContainer.classList.add("active");
  contentBox.focus();
  window.addEventListener("click", backgroundClickHandler);
}

function hideNoteForm() {
  formContainer.classList.remove("active");
  window.removeEventListener("click", backgroundClickHandler);
}

function noteFormIsShown() {
  return formContainer.classList.contains("active");
}

function backgroundClickHandler(event) {
  let modal = formContainer.querySelector(".modal");
  if (!modal.contains(event.target)
      // HACK: the problem is that upon clicking the note form button the modal
      // is opened and also immediately closed again
      && event.target !== document.querySelector(".note-form-button")) {
    hideNoteForm();
  }
}

function submitNoteForm() {
  let content = encodeURIComponent(contentBox.value);
  sendBackendRequest("/post", `content=${content}`, data => {
    hideNoteForm();
    contentBox.value = "";
    prependToNoteList(data);
    showNotification("Note saved.");
  });
}
