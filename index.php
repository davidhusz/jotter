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
		<title>Notes</title>
	</head>
	<body>
	    <div class="wrapper">
	        <header>
                <h1>Notes</h1>
            </header>
            <noscript>
                You have disabled JavaScript. Interactive components, such as the copy or the delete function, will not work correctly.
            </noscript>
            <main>
	            <div class="note-list">
	                <?php
                        foreach ($notes as $note) {
                            echo $note->full_html();
                        }
	                ?>
                </div>
            </main>
            <div class="notification"></div>
        </div>
	</body>
</html>
