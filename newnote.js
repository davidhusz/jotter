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
    let noteForm = document.querySelector(".new-note");
    noteForm.classList.add("active");
    let contentBox = noteForm.querySelector("textarea");
    contentBox.focus();
    contentBox.addEventListener("keydown", function(event) {
      if (event.ctrlKey && event.key == "Enter") {
        noteForm.querySelector("form").submit();
      }
    });
}

function hideNoteForm() {
  let noteForm = document.querySelector(".new-note");
  noteForm.classList.remove("active");
}
