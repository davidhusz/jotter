<?php
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
?>
