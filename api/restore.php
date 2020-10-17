<?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // File restoration
        if (isset($_POST["file"])) {
            $fname = $_POST["file"];
            $fpath = "../contents/trash/$fname";
            $contents = "../contents";
            if (file_exists($fpath)) {
                rename($fpath, "$contents/$fname");
                // echo "Successfully restored $fname\n";
            } else {
                http_response_code(404);
                // echo "File $fname does not exist\n";
            }
        }
    }
?>
