<?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Global variables
        $dir = "../contents";
        $date = date("U");
        
        // Text upload
        if (isset($_POST["note"])) {
            $note = $_POST["note"];
            $file = fopen("$dir/$date.txt", "x");
            fwrite($file, "$note\n");
            fclose($file);
            // TODO: add random id to these file names as well
        }
        
        // Files upload
        elseif (isset($_FILES["contents"])) {
            $contents = $_FILES["contents"];
            $fcount = count($contents["name"]);
            
            for ($i = 0; $i < $fcount; $i++) {
                if ($contents["error"][$i] == 0) {
                    $fname = $contents["name"][$i];
                    $ftype = $contents["type"][$i];
                    $fsource = $contents["tmp_name"][$i];
                    
                    // File type & extension inference
                    if (preg_match("/\.([a-z0-9]{1,5})$/", $fname, $match)) {
                        // if the original file name has something that looks
                        // like a file extension, take that instead of trying
                        // to infer it
                        $ext = $match[1];
                    } else {
                        if ($ftype == "application/octet-stream") {
                            // if file type is unknown, try to infer it
                            $ftype = mime_content_type($fsource);
                        }
                        if ($ftype == "text/plain") {
                            // plain text files are the only instance
                            // I'm aware of where the part after the slash
                            // in the mime type is not the same as the standard
                            // file extension
                            $ftype = "text/txt";
                        }
                        list($ftype, $ext) = explode("/", $ftype);
                        if (!preg_match("/^[a-z0-9]{1,5}$/", $ext)) {
                            // if it doesn't look like a file extension, discard it
                            $ext = "file";
                        }
                    }
                    
                    /*
                    // number of files with the same date identifier
                    $samedate = count(glob("$dir/$date*.$ext"));
                    
                    if ($samedate == 0) {
                        $fdest = "$dir/$date.$ext";
                    } else {
                        $fdest = "$dir/$date-" . ($samedate + 1) . ".$ext";
                        if (file_exists("$dir/$date.$ext")) {
                            rename("$dir/$date.$ext", "$dir/$date-1.$ext");
                        }
                    }
                    */
                    
                    $fdest = $fsource;
                    
                    while (file_exists($fdest)) {
                        // random five digit integer
                        $random_id = str_pad(rand(0, 99999), 5, 0, STR_PAD_LEFT);
                        $fdest = "$dir/$date-$random_id.$ext";
                        // maybe use `uniqid()` instead?
                    }
                    
                    move_uploaded_file($fsource, $fdest);
                }
            }
        }
    }
?>
