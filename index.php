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
	    <div class="main">
            <h1>Notes</h1>
            <noscript>
                You have disabled JavaScript. Interactive components, such as the copy or the delete function, will not work correctly.
            </noscript>
	        <div class="note-list">
	            <?php
	                require "note.class.php";
	                $fnames = scandir("contents", SCANDIR_SORT_DESCENDING);
                    foreach ($fnames as $fname) {
                        $fpath = "contents/$fname";
                        // ignore hidden directories and files
                        if ($fname[0] != ".") {
                            $note = Note::of_unknown_type($fpath);
                            echo $note->full_html();
                        }
                    }
	            ?>
            </div>
            <div class="notification"></div>
        </div>
	</body>
</html>
