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
    formContainer.classList.add("active");
    let contentBox = formContainer.querySelector("textarea");
    contentBox.focus();
    contentBox.addEventListener("keydown", function(event) {
      if (event.ctrlKey && event.key == "Enter") {
        formContainer.querySelector("form").submit();
      }
    });
}

function hideNoteForm() {
  let formContainer = document.querySelector(".new-note");
  formContainer.classList.remove("active");
}

