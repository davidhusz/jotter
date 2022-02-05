function selectNote(note) {
  unselectAllNotes();
  note.container.classList.add("selected");
  note.container.scrollIntoView({
    behavior: "smooth",
    block: "nearest"
  });
}

function unselectNote(note) {
  note.container.classList.remove("selected");
}

function isNoteSelected(note) {
  return note.container.classList.contains("selected");
}

function isAnyNoteSelected() {
  return noteList.some(note => isNoteSelected(note));
}

function getSelectedNote() {
  return noteList.filter(note => isNoteSelected(note))[0];
}

function unselectAllNotes() {
  if (isAnyNoteSelected()) {
    unselectNote(getSelectedNote());
  }
}

window.addEventListener("keydown", function(event) {
  if (!noteFormIsShown() && !(event.ctrlKey || event.altKey || event.shiftKey)) {
    switch (event.key) {
      case "ArrowDown":
      case "f":
        event.preventDefault();
        if (!isAnyNoteSelected()) {
          selectNote(noteList[0]);
        } else {
          let currentlySelected = noteList.indexOf(getSelectedNote());
          if (currentlySelected < noteList.length-1) {
            selectNote(noteList[currentlySelected+1]);
          } else {
            unselectAllNotes();
          }
        }
        break;
      
      case "ArrowUp":
      case "w":
        if (isAnyNoteSelected()) {
          event.preventDefault();
          let currentlySelected = noteList.indexOf(getSelectedNote());
          if (currentlySelected > 0) {
            selectNote(noteList[currentlySelected-1]);
          } else {
            unselectAllNotes();
          }
        }
        break;
      
      case "Escape":
        unselectAllNotes();
        break;
      
      case "c":
        if (isAnyNoteSelected()) {
          copyNoteToClipboard(getSelectedNote());
        }
        break;
      
      case "d":
        if (isAnyNoteSelected()) {
          getSelectedNote().container.querySelector(".download").click();
        }
        break;
      
      case "b":
        if (isAnyNoteSelected()) {
          bumpNote(getSelectedNote());
        }
        break;
      
      case "t": // "t" for trash
        if (isAnyNoteSelected()) {
          removeNote(getSelectedNote());
        }
        break;
    }
  }
});
