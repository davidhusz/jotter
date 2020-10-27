<?php
    require "note.class.php";
    $fnames = scandir("contents", SCANDIR_SORT_DESCENDING);
    if (isset($_GET["count"])) {
        $fnames = array_slice($fnames, 0, $_GET["count"]);
    }
    $notes = [];
    foreach ($fnames as $fname) {
        $fpath = "contents/$fname";
        // ignore hidden directories and files
        if ($fname[0] != ".") {
            $notes[] = Note::of_unknown_type($fpath);
        }
    }
    if ($_SERVER["HTTP_ACCEPT"] == "application/json") {
        header("Content-Type: application/json;charset=utf-8");
        foreach ($notes as $note) {
            echo $note->content_as_json()."\n";
        }
        exit();
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <link rel="icon" type="image/png" href="favicon.png">
        <link rel="stylesheet" href="style.css">
        <script type="text/javascript" src="controls.js"></script>
        <script type="text/javascript" src="newnote.js"></script>
        <title>Notes</title>
    </head>
    <body>
        <div class="main-wrapper">
            <header>
                <h1>Notes</h1>
                <noscript>
                    You have disabled JavaScript. Interactive components, such as the copy or the delete function, will not work correctly.
                </noscript>
                <div class="instructions">
                    click <span class="clickable note-form-button">here</span> or press <kbd>N</kbd> to create a new note
                </div>
            </header>
            <main>
                <div class="note-list">
                    <?php
                        foreach ($notes as $note) {
                            echo $note->full_html();
                        }
                    ?>
                </div>
            </main>
            <div class="new-note">
                <div class="wrapper1-relative">
                    <div class="shadow-background"></div>
                    <div class="wrapper2-absolute">
                        <div class="wrapper3-relative">
                            <div class="modal">
                                <form action="api/post.php" method="post">
                                    <div>
                                        <label for="form-contentbox">
                                            Please enter a new note:
                                        </label>
                                        <span class="clickable close-button">X</span>
                                    </div>
                                    <div class="contentbox">
                                        <textarea id="form-contentbox" name="note"></textarea>
                                    </div>
                                    <div class="submit-button">
                                        Press <kbd>Ctrl</kbd>+<kbd>Enter</kbd> or click
                                        <button type="submit">Save</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="notification"></div>
        </div>
    </body>
</html>
