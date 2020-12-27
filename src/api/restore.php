<?php
require "assertions.php";
assert_http_method();
assert_http_parameters("id");
$fpath = get_path_from_id($_POST["id"]);
$fname = basename($fpath);
$contents = "../contents";
rename($fpath, "$contents/$fname");
?>
