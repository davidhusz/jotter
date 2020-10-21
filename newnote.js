"use strict";

window.addEventListener("keydown", function(event) {
  if (event.key == "n") {
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
}

function hideNoteForm() {
  let formContainer = getNoteForm();
  formContainer.classList.remove("active");
}

function submitNoteForm() {
  let form = getNoteForm().querySelector("form");
  // change this so it submits the form without leaving the page
  form.submit();
}
