<?php
// * Error reporting - ALL
error_reporting(E_ALL);
ini_set("display_errors", 1);
// Error reporting - NONE
// error_reporting(0);
// ini_set('display_errors', 0);

// set content type
header('Content-Type: application/json');

define('MYSQL_SERVER', 'localhost');
define('MYSQL_USER', 'root');
define('MYSQL_PASSWORD', '');
define('MYSQL_DB', 'm307_timo');
// SQL Table
define('DATA_SOURCE', 'test');

// get Parameters
$id = @$_GET['id'];
$action = @$_GET['action'];
$limit = @$_GET['limit'];

// initialize var
// $con = null;

initializeDB();

// * Route handling
switch ($action) {
    case 'get':
        getData();
        break;
    case 'update':
        updateData();
        break;
    case 'create':
        insertData();
        break;
    case 'delete':
        deleteData();
        break;
    default:
        apiResponse(array(), array('Route not found'), array(), 404);
        break;
}

// ! close connection; exit API
// ! if no apiResponse was sent
// ! called only when never exited
exitAPI();



// * CRUD Functions below

/**
 * initializeDB()
 * initialize DB and create connection
 */
function initializeDB()
{
    global $con;

    // connect to db
    if ($con = mysqli_connect(MYSQL_SERVER, MYSQL_USER, MYSQL_PASSWORD)) {
        // check if db exists
        if (!($con->select_db(MYSQL_DB))) {
            // * create DB
            $query = "CREATE DATABASE IF NOT EXISTS " . MYSQL_DB . " DEFAULT CHARACTER SET utf8;";
            $con->query($query);
            // * apply DB; check if db was created
            if (!($con->select_db(MYSQL_DB))) {
                // ! fatal error;
                // ! user has no create rights
                apiResponse(array(), array('User has no DB create rights. Manual creation needed'), array(), 400);
                // force exit API
                exitAPI();
            }


            // * create Schema
            $schema = "CREATE TABLE IF NOT EXISTS test (
                id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                text VARCHAR(255) NOT NULL,
                lang VARCHAR(255) NOT NULL
            );";
            $con->query($schema);


            // * insert Data
            $data = "INSERT INTO test(text,lang) VALUES
                ('Hello World','english'),
                ('Hallo World','deutsch'),
                ('Bonjour le monde','française'),
                ('Hola Mundo','español');";
            $con->query($data);
        }
    } else {
        // ! fatal error;
        // ! cannot connect to db
        apiResponse(array(), array('No connection to DB possible'), array(), 400);
        // force exit API
        exitAPI();
    }
}

/**
 * getData()
 * get data from SQL DB
 * `CRUD: READ`
 */
function getData()
{
    global $con;
    global $limit;

    apiResponse(getDataArray(), array(), array(), 200);
}

/**
 * getDataArray()
 * get **only** the array for data from SQL DB -
 * **NO CURD Endpoint**
 */
function getDataArray()
{
    global $con;
    global $limit;

    if (isset($limit) && !empty($limit)) {
        // only select the limit rows
        $query = "SELECT * FROM " . DATA_SOURCE . " LIMIT " . $limit . ";";
    } else {
        // no limit was passed
        $query = "SELECT * FROM " . DATA_SOURCE . ";";
    }

    $result = $con->query($query);

    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * insertData()
 * insert data into DB
 * `CRUD: CREATE`
 */
function insertData()
{
    global $id;
    global $con;

    // get data from arguments
    $text = @$_POST['text'];
    $lang = @$_POST['lang'];

    // check for validity (e.g. was sent via POST)
    if (isset($text) && isset($lang)) {
        $query = "INSERT INTO " . DATA_SOURCE . "(text,lang) VALUE ('" . $text . "','" . $lang . "');";
        $con->query($query);

        apiResponse(getDataArray(), array(), array("Successfully created Record."), 200);
    } else {
        apiResponse(array(), array('Not all arguments specified'), array(), 400);
    }
}

/**
 * updateData()
 * update data in DB using `$id`
 * `CRUD: UPDATE`
 */
function updateData()
{
    global $id;
    global $con;

    // get data from arguments
    $text = @$_POST['text'];
    $lang = @$_POST['lang'];

    // check for validity (e.g. was sent via POST)
    if (isset($text) && isset($lang)) {
        $query = "UPDATE " . DATA_SOURCE . " SET text='" . $text . "', lang='" . $lang . "' WHERE id=" . $id . ";";
        $con->query($query);

        apiResponse(getDataArray(), array(), array("Successfully created Record."), 200);
    } else {
        apiResponse(array(), array('Not all arguments specified'), array(), 400);
    }
}

/**
 * deleteData()
 * delete data in DB using `$id`
 * `CRUD: DELETE`
 */
function deleteData()
{
    global $id;
    global $con;

    // check if id was set
    if (isset($id) && !empty($id)) {
        $query = "DELETE FROM " . DATA_SOURCE . " WHERE id=" . $id . ";";
        $con->query($query);

        apiResponse(getDataArray(), array(), array('Successfully deleted record \'' . $id . '\''), 200);
    } else {
        apiResponse(array(), array('Cannot delete. Argument \'id\' missing'), array(), 400);
    }
}



// * API functions below

/**
 * apiResponse($data: Array, $error: Array, $success: Array, $status: int)
 * return a formated json
 * @see https://httpstatuses.com/
 */
function apiResponse($data, $error, $success, $status = 200)
{
    // status code must be sent before first content
    http_response_code($status);

    $response = array('data' => $data, 'error' => $error, 'success' => $success);
    echo json_encode($response);

    // exit API because after content send nothing happens
    exitAPI();
}

/**
 * exitAPI()
 * exit API when fatal error
 * also end of API file to close mysql connection
 */
function exitAPI()
{
    global $con;

    $con->close();
    exit(); // only needed when force quit; no problem when end of file
}
