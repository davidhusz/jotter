"use strict";

window.addEventListener("keydown", function(event) {
  if (event.key == "n") {
    event.preventDefault();
    showNoteForm();
  } else if (event.key == "Escape") {
    hideNoteForm();
  }
});

function showNoteForm() {
    let formContainer = document.querySelector(".new-note");
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
  let formContainer = document.querySelector(".new-note");
  formContainer.classList.remove("active");
}

function submitNoteForm() {
  let form = document.querySelector(".new-note form");
  // change this so it submits the form without leaving the page
  form.submit();
}
