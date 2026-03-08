
<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');

// Example usage
$number = 10500;
// Example usage
try {
    $no = new Number($number);
    echo $no->convertToWords() . PHP_EOL;
} catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
}