<?php
require "assertions.php";
assert_http_method();
assert_http_parameters("file");
$fname = $_POST["file"];
$fpath = "../contents/.trash/$fname";
$contents = "../contents";
if (file_exists($fpath)) {
    rename($fpath, "$contents/$fname");
} else {
    http_response_code(404);
    echo "File '$fname' does not exist\n";
}
?>
