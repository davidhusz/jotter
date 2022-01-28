<?php
function get_unique_filename($extension, $original_filename = "") {
    // Return a file name which is unique in the contents directory
    // (regardless of extension or original file name)
    $dir = "../contents";
    $date = date("YmdHis");
    
    $id_collisions = 1;
    while ($id_collisions > 0) {
        // Random five digit integer
        $id = "$date-" . str_pad(rand(0, 99999), 5, 0, STR_PAD_LEFT);
        $id_collisions = count(glob("$dir/$id.*"))
                       + count(glob("$dir/.trash/$id.*"));
    }
    
    if (!empty($original_filename)) {
        // Problematic characters will be removed from file name
        $destination = "$dir/$id-"
                       . preg_replace("/[^A-Za-z0-9_-]/", "", $original_filename)
                       // allow dots too maybe?
                       . ".$extension";
    } else {
        $destination = "$dir/$id.$extension";
    }
    
    return $destination;
}

require "common.php";
assert_http_method();

// Text upload
if (!isset($_FILES["content"])) {
    assert_http_parameters("content");
    // TODO: account for possibility of multiple content fields
    $content = $_POST["content"];
    $fdest = get_unique_filename("txt");
    file_put_contents($fdest, "$content\n");
}

// TODO: allow for simultaneous upload of text and file notes

// Files upload
else {
    $content = $_FILES["content"];
    if (!is_array($content["tmp_name"])) {
        // If the user uploads just one file (not a list of files with one
        // element, i.e. what you would get by using curl syntax
        // `content[]=@file`), we turn it into such a one element file list here
        // so that we don't have to account for it later on
        $content = array_map(function($prop) {
            return [$prop];
        }, $content);
    }
    $fcount = count($content["tmp_name"]);
    
    for ($i = 0; $i < $fcount; $i++) {
        if ($content["error"][$i] == 0) {
            $fname = $content["name"][$i];
            $ftype = $content["type"][$i];
            $fsource = $content["tmp_name"][$i];
            
            // File type & extension inference
            // you really need to be more lenient with the extension regex
            // (capital letters, longer extensions)
            // TODO: actually, just respect the original filename, it's not hard
            if (preg_match("/(.+)\.([a-z0-9]{1,5})$/", $fname, $match)) {
                // If the original file name has something that looks
                // like a file extension, take that instead of trying
                // to infer it
                $fname = $match[1];
                $ext = $match[2];
            } else {
                $fname = "";
                if (empty($ftype) || $ftype == "application/octet-stream") {
                    // If the file type is unknown, try to infer it
                    $ftype = mime_content_type($fsource);
                }
                if ($ftype == "text/plain") {
                    // Ensure plain text files have a .txt extension
                    // TODO: actually, use /etc/mime.types here
                    $ftype = "text/txt";
                }
                list($ftype, $ext) = explode("/", $ftype);
                if (!preg_match("/^[a-z0-9]{1,5}$/", $ext)) {
                    // If it doesn't look like a file extension, discard it and
                    // default to 'file'
                    $ext = "file";
                }
            }
            
            $fdest = get_unique_filename($ext, $fname);
            move_uploaded_file($fsource, $fdest);
        }
    }
}

http_response_code(201); // 201 Created
require "../includes/noteclasses.php";
// TODO: return all new notes here in case multiple were created, not just the
// last one
$note = Note::of_unknown_type($fdest);
if (!preg_match("/^text\/html/", $_SERVER["HTTP_ACCEPT"])) {
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode($note->get_info())."\n";
} else {
    echo $note->as_html()."\n";
}
