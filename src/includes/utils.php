<?php
define("APP_ROOT", realpath(dirname(__FILE__) . "/.."));
define("CONTENT_DIR", APP_ROOT . "/contents");
require APP_ROOT . "/includes/noteclasses.php";

function errorResponse($status, $message = "") {
    http_response_code($status);
    if (!empty($message)) {
        header("Content-Type: text/plain");
        echo "$message\n";
    } else {
        // Remove the Content-Type header since the response body will be empty
        header("Content-Type:");
    }
    exit();
}

function assert_http_method($allowed_methods = ["POST"]) {
    if (!in_array($_SERVER["REQUEST_METHOD"], $allowed_methods)) {
        header("Allow: ".implode(", ", $allowed_methods));
        // 405 Method Not Allowed
        errorResponse(405);
    }
}

function assert_required_http_parameters(...$params) {
    foreach ($params as $param) {
        if (!isset($_POST[$param])) {
            // 422 Unprocessable Entity
            errorResponse(422,
                "The following parameters are required: ".implode(", ", $params)
            );
        }
    }
}

function get_note_paths($location, $id = "") {
    if ($location == "main") {
        $dir = ".";
    } elseif ($location == "all") {
        return array_merge(
            get_note_paths("main", $id),
            get_note_paths("[A-Za-z0-9]*", $id)
        );
    } else {
        $dir = ".$location";
    }
    return array_map('realpath', glob(CONTENT_DIR . "/$dir/$id*"));
}

function get_note_path_from_id($id, $location = "all") {
    if (!preg_match("/^\d{14}-\d{5}$/", $id)) {
        errorResponse(404, "'$id' is not a valid note id");
    }
    $matches = get_note_paths($location, $id);
    $note_description =
        "with id $id" .
        ($location === "all" ? "" : " in this location") .
        ".";
    if (count($matches) == 1) {
        return $matches[0];
    } elseif (count($matches) == 0) {
        // 404 Not Found
        errorResponse(404, "There is no note $note_description");
    } else {
        // 500 Internal Server Error
        errorResponse(500, "There are multiple notes $note_description");
    }
}

function render_notes($fpaths) {
    $notes = [];
    foreach ($fpaths as $fpath) {
        $notes[] = Note::of_unknown_type($fpath);
    }
    if (!preg_match("/^text\/html/", $_SERVER["HTTP_ACCEPT"])) {
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode([
            "notes" => array_map(function($note) {
                return $note->get_info();
            }, $notes),
        ])."\n";
        return true;
    } elseif ($_SERVER["HTTP_ACCEPT"] == "text/html;fragment=true") {
        foreach ($notes as $note) {
            echo $note->as_html();
        }
        return true;
    } else {
        // not implemented
        return false;
    }
}

function render_raw_note($fpath, $fetch) {
    $note = Note::of_unknown_type($fpath);
    $mime_type = mime_content_type($fpath);
    $fetch_dispositions = [
        "raw" => "inline",
        "download" => "attachment"
    ];
    $disposition = $fetch_dispositions[$_GET["fetch"]];
    $fname_quoted = '"' . str_replace('"', '\"', $note->fname) . '"';
    $date = gmdate("D, d M Y H:i:s T", $note->last_modified);
    $fhandle = fopen($fpath, 'rb');
    header("Content-Type: $mime_type");
    header("Content-Length: $note->fsize");
    header("Content-Disposition: $disposition; filename=$fname_quoted");
    header("Last-Modified: $date");
    fpassthru($fhandle);
}
