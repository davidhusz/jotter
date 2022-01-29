<?php
define("APP_ROOT", realpath(dirname(__FILE__) . "/.."));
define("CONTENT_DIR", APP_ROOT . "/contents");
require APP_ROOT . "/includes/noteclasses.php";

function assert_http_method($allowed_methods = ["POST"]) {
    if (!in_array($_SERVER["REQUEST_METHOD"], $allowed_methods)) {
        http_response_code(405);  // 405 Method Not Allowed
        // Remove the Content-Type header since the response body will be empty
        header("Content-Type:");
        header("Allow: " . implode(", ", $allowed_methods));
        exit();
    }
}

function assert_required_http_parameters(...$params) {
    foreach ($params as $param) {
        if (!isset($_POST[$param])) {
            http_response_code(422);  // 422 Unprocessable Entity
            header("Content-Type: text/plain");
            echo "The following parameters are required: "
                  . implode(", ", $params) . "\n";
            exit();
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

function get_path_from_id($id) {
    // TODO: assert that $id is a valid note id
    $matches = get_note_paths("all", $id);
    if (count($matches) == 1) {
        return $matches[0];
    } elseif (count($matches) == 0) {
        http_response_code(404);  // 404 Not Found
        header("Content-Type: text/plain");
        echo "There is no note with id $id\n";
        exit();
    } else {
        http_response_code(500);  // 500 Internal Server Error
        header("Content-Type: text/plain");
        echo "There are multiple notes with id $id\n";
        exit();
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
