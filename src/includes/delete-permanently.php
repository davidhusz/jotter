<?php
require "utils.php";
assert_http_method();
assert_required_http_parameters("id");
$fpath = get_path_from_id($_POST["id"]);
// We have to respond with the note before the actual deletion since afterwards
// we wouldn't be able to find it anymore
render_notes([$fpath]);
unlink($fpath);
