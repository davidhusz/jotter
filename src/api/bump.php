<?php
require "common.php";
assert_http_method();
assert_http_parameters("id");
$fpath = get_path_from_id($_POST["id"]);
touch($fpath);
require "../includes/noteclasses.php";
$note = Note::of_unknown_type($fpath);
if (!preg_match("/^text\/html/", $_SERVER["HTTP_ACCEPT"])) {
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode($note->get_info())."\n";
} else {
    echo $note->as_html()."\n";
}
