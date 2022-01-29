<?php
require "common.php";
assert_http_method();
assert_required_http_parameters("id");
$fpath = get_path_from_id($_POST["id"]);
$fname = basename($fpath);
$fpath_new = CONTENT_DIR . "/$fname";
rename($fpath, $fpath_new);
render_notes([$fpath_new]);
