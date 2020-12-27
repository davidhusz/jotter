<?php
require "assertions.php";
assert_http_method();
assert_http_parameters("file");
$fname = $_POST["file"];
$fpath = "../contents/$fname";
$trash = "../contents/.trash";
if (file_exists($fpath)) {
    rename($fpath, "$trash/$fname");
} else {
    http_response_code(404);
    echo "File '$fname' does not exist\n";
}
?>
