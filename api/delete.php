<?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // File deletion
        if (isset($_POST["file"])) {
            $fname = $_POST["file"];
            $fpath = "../contents/$fname";
            $trash = "../contents/.trash";
            if (file_exists($fpath)) {
                rename($fpath, "$trash/$fname");
                // echo "Successfully deleted $fname\n";
                // TODO: delete (don't just move) file from cache (once you've implemented caching)
            } else {
                http_response_code(404);
                // echo "File $fname does not exist\n";
            }
        }
    /*
    } elseif ($_SERVER["REQUEST_METHOD"] == "DELETE") {
        echo "You're trying to delete me!\n";
    */
    }
    // TODO: return disallowed status for other request methods
?>
