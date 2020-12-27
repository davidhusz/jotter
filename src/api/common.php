<?php
define("CONTENT_DIR", "../contents");

function assert_http_method($allowed_methods = ["POST"]) {
    if (!in_array($_SERVER["REQUEST_METHOD"], $allowed_methods)) {
        http_response_code(405);  // 405 Method Not Allowed
        header("Allow: " . implode(", ", $allowed_methods));
        exit();
    }
}

function assert_http_parameters(...$params) {
    foreach ($params as $param) {
        if (!isset($_POST[$param])) {
            http_response_code(422);  // 422 Unprocessable Entity
            echo "The following parameters are required: "
                  . implode(", ", $params) . "\n";
            exit();
        }
    }
}

function get_path_from_id($id) {
    // TODO: assert that $id is a valid note id
    $matches = glob(CONTENT_DIR . "/.*/$id*");
    // `.*/` matches `./` as well
    if (count($matches) == 1) {
        return $matches[0];
    } elseif (count($matches) == 0) {
        http_response_code(404);  // 404 Not Found
        echo "There is no note with id $id\n";
        exit();
    } else {
        http_response_code(500);  // 500 Internal Server Error
        echo "There are multiple notes with id $id\n";
        exit();
    }
}
?>
