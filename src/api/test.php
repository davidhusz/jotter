<?php
echo "You issued a $_SERVER[REQUEST_METHOD] request to $_SERVER[REQUEST_URI]\n";
echo "Request info:\n";
print_r($_REQUEST);
echo "Files info:\n";
print_r($_FILES);
?>
