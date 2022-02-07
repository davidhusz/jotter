<?php
require "utils.php";
performNoteOperation(function($fpath) {
    touch($fpath);
    return $fpath;
});
