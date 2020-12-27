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

require "assertions.php";
assert_http_method();

// Text upload
if (!isset($_FILES["content"])) {
    assert_http_parameters("content");
    $content = $_POST["content"];
    $fdest = get_unique_filename("txt");
    file_put_contents($fdest, "$content\n");
}

// Files upload
else {
    $content = $_FILES["content"];
    $fcount = count($content["name"]);
    
    for ($i = 0; $i < $fcount; $i++) {
        if ($content["error"][$i] == 0) {
            $fname = $content["name"][$i];
            $ftype = $content["type"][$i];
            $fsource = $content["tmp_name"][$i];
            
            // File type & extension inference
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
                    $ftype = "text/txt";
                }
                list($ftype, $ext) = explode("/", $ftype);
                if (!preg_match("/^[a-z0-9]{1,5}$/", $ext)) {
                    // If it doesn't look like a file extension, discard it
                    $ext = "file";
                }
            }
            
            $fdest = get_unique_filename($ext, $fname);
            move_uploaded_file($fsource, $fdest);
        }
    }
}
?>
