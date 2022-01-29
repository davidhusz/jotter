<?php
    require "api/common.php";
    if (!isset($_GET["id"])) {
        $fpaths = get_note_paths($_GET["location"] ?? "main");
        usort($fpaths, function($file1, $file2) {
            // sort by modification time (newest to oldest);
            return filemtime("$file2") - filemtime("$file1");
        });
        // Slice array according to `count` and `skip` URL parameters
        $fpaths = array_slice($fpaths, $_GET["skip"] ?? 0, $_GET["count"] ?? null);
    } else {
        $fpaths = [get_path_from_id($_GET["id"])];
    }
    render_notes($fpaths) and exit();
    // Since rendering the notes as a full document is not implemented by the
    // above function yet, we have to do it here
    $notes = [];
    foreach ($fpaths as $fpath) {
        $notes[] = Note::of_unknown_type($fpath);
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <link rel="icon" type="image/png" href="/includes/favicon.png">
        <link rel="stylesheet" href="/includes/style.css">
        <script type="text/javascript" src="/includes/notecontrols.js"></script>
        <script type="text/javascript" src="/includes/newnoteform.js"></script>
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
                            echo $note->as_html();
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
