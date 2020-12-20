<?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST["file"])) {
            $fname = $_POST["file"];
            $fpath = "../contents/$fname";
            if (file_exists($fpath)) {
                touch($fpath);
            } else {
                http_response_code(404);
            }
        }
    }
?>
