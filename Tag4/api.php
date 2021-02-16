<?php

// WHAT TO CHECK
// required field filled
// length of input to match db
// email

$write = false;

$field = $_GET['field'];
$field = trim($field); // "             abc" => "abc"

echo "before: " . $field . "<br>";

echo "<hr>";

if (strstr($field, '@')) {

    $array = explode('@', $field);

    $field = htmlspecialchars($field); // that website does not apply html tags

    echo "special chars: " . $field . "<br>";
    echo "<hr>";

    if (filter_var($field, FILTER_VALIDATE_EMAIL)) {
        // email valid
        if (checkdnsrr($array[1], 'MX')) {
            echo 'mailserver valid' . "<br>";
            $write = true;
        } else {
            echo 'mailserver NOT Valid' . "<br>";
        }
    } else {
        echo "email NOT valid";
    }
} else {
    
}

echo "<hr>";

if ($write == true) {
    echo "INFO: SQL INSERT";
}
