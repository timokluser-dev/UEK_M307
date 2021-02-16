<?php

if ($_GET['json'] == 'true') {
    echo json_encode(
        array('$_GET' => $_GET, '$_POST' => $_POST)
    );
} else {
    echo '$_GET';
    print_r($_GET);

    echo '$_POST';
    print_r($_POST);
}
