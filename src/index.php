<?php
require "includes/utils.php";
if (!isset($_GET["id"])) {
    $location = $_GET["location"] ?? "main";
    $fpaths = get_note_paths($location);
    usort($fpaths, function($file1, $file2) {
        // sort by modification time (newest to oldest);
        return filemtime("$file2") - filemtime("$file1");
    });
    if (isset($_GET["before"])) {
        $before_path = get_note_path_from_id($_GET["before"], $location);
        $length = array_search($before_path, $fpaths);
    }
    if (isset($_GET["after"])) {
        $after_path = get_note_path_from_id($_GET["after"], $location);
        $offset = array_search($after_path, $fpaths) + 1;
    }
    // Slice array according to `before` and `after` URL parameters
    $fpaths = array_slice($fpaths, $offset ?? 0, $length ?? null);
    // Slice array according to `count` and `skip` URL parameters
    $fpaths = array_slice($fpaths, $_GET["skip"] ?? 0, $_GET["count"] ?? null);
} else {
    $fpath = get_note_path_from_id($_GET["id"]);
    if (!isset($_GET["fetch"])) {
        $fpaths = [$fpath];
    } else {
        render_raw_note($fpath, $_GET["fetch"]);
        exit();
    }
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
        <link rel="icon" type="image/png" href="/assets/favicon.png">
        <link rel="stylesheet" href="/assets/style.css">
        <script type="text/javascript" src="/assets/notecontrols.js"></script>
        <script type="text/javascript" src="/assets/newnoteform.js"></script>
        <script type="text/javascript" src="/assets/hotkeys.js"></script>
        <title>Notes</title>
    </head>
    <body>
        <div class="main-wrapper">
            <header>
                <h1>Notes</h1>
                <noscript>
                    You have disabled JavaScript. Interactive components, such as the copy or the trash function, will not work correctly.
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
                                <form action="/post" method="post">
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
