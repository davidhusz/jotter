<?php
require "common.php";
assert_http_method();
assert_required_http_parameters("id");
$fpath = get_path_from_id($_POST["id"]);
touch($fpath);
render_notes([$fpath]);
