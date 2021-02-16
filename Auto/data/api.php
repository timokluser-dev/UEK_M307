<?php
// * Error reporting - ALL
// error_reporting(E_ALL);
// ini_set("display_errors", 1);
// Error reporting - NONE
error_reporting(0);
ini_set('display_errors', 0);

// set content type json
header('Content-Type: application/json');

define('MYSQL_SERVER', 'localhost');
define('MYSQL_USER', 'root');
define('MYSQL_PASSWORD', '');
define('MYSQL_DB', 'm307_cars');
// SQL Table
define('DATA_SOURCE', 'cars');

// get Parameters
$id = @$_GET['id'];
$action = @$_GET['action'];
$limit = @$_GET['limit'];

// initialize var
// $con = null;

// before all initialize DB
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
    case 'tanken':
        // Special Case
        $query = "UPDATE " . DATA_SOURCE . " SET tank=100 WHERE id=" . $id . ";";
        $con->query($query);
        apiResponse(getDataArray(), array(), array("Car '" . $id . "' was fully loaded"), 200);
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
            $schema = "CREATE TABLE IF NOT EXISTS cars (
                id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                bauart VARCHAR(255) NOT NULL,
                kraftstoff VARCHAR(255) NOT NULL,
                color VARCHAR(7) NOT NULL,
                tank INT NOT NULL,
                additional_information VARCHAR(1024) NULL
            );";
            $con->query($schema);


            // * insert Data
            // * ALWAYS **3 Rows** as example
            $data = "INSERT INTO cars(name,bauart,kraftstoff,color,tank) VALUES
                ('VW Passat','Limousine','Diesel','#808080',0),
                ('TESLA Model S','Limousine','Elektro','#ffffff',0);";
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
    global $id;

    // first check for id
    if (isset($id) && !empty($id)) {
        $query = "SELECT * FROM " . DATA_SOURCE . " WHERE id =" . $id . ";";
    } else {
        // check if limit is set & limit is no 0 (= all records)
        if (isset($limit) && !empty($limit) && @($limit != 0)) {
            // only select the limit rows
            $query = "SELECT * FROM " . DATA_SOURCE . " LIMIT " . $limit . ";";
        } else {
            // no limit was passed
            $query = "SELECT * FROM " . DATA_SOURCE . ";";
        }
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

    $write = true;

    // get data from arguments
    $name = @$_POST['name'];
    $email = @$_POST['email'];
    $bauart = @$_POST['bauart'];
    $kraftstoff = @$_POST['kraftstoff'];
    $color = @$_POST['color'];
    $tank = @$_POST['tank'];

    // check for validity (e.g. was sent via POST)
    if (isset($name) && isset($bauart) && isset($kraftstoff) && isset($color) && isset($tank) && isset($email)) {
        // 1. prepare
        $name = validate_prepare($name);
        $email = validate_prepare($email);
        $bauart = validate_prepare($bauart);
        $kraftstoff = validate_prepare($kraftstoff);
        $color = validate_prepare($color);
        $tank = validate_prepare($tank);

        // 2. validate
        if (!validate_lengthRequired($name)) $write = false;
        if (!validate_lengthRequired($email)) $write = false;
        if (!validate_lengthRequired($bauart)) $write = false;
        if (!validate_lengthRequired($kraftstoff)) $write = false;
        if (!validate_lengthRequired($color)) $write = false;
        if (!validate_lengthRequired($tank)) $write = false;

        // 3. validate email
        if (!validate_email($email) || validate_blacklistedDomain($email)) {
            $write = false;
        }

        // ! further validations (e.g. number range)
        // if ( ... ) {
        //     $write = false;
        // }

        // if insert check successful
        if ($write) {
            $query = "INSERT INTO cars(name,bauart,kraftstoff,color,tank) VALUES ('" . $name . "','" . $bauart . "','" . $kraftstoff . "','" . $color . "'," . $tank . ");";
            $con->query($query);

            apiResponse(getDataArray(), array(), array("Successfully created Record."), 200);
        } else {
            apiResponse(getDataArray(), array('Validation failed'), array(), 400);
        }
    } else {
        apiResponse(array(), array('Not all arguments specified or not with POST'), array(), 400);
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

    $write = true;

    // get data from arguments
    $name = @$_POST['name'];
    $email = @$_POST['email'];
    $bauart = @$_POST['bauart'];
    $kraftstoff = @$_POST['kraftstoff'];
    $color = @$_POST['color'];
    $tank = @$_POST['tank'];

    // check for validity (e.g. was sent via POST)
    if (isset($name) && isset($bauart) && isset($kraftstoff) && isset($color) && isset($tank) && isset($email)) {
        // 1. prepare
        $name = validate_prepare($name);
        $email = validate_prepare($email);
        $bauart = validate_prepare($bauart);
        $kraftstoff = validate_prepare($kraftstoff);
        $color = validate_prepare($color);
        $tank = validate_prepare($tank);

        // 2. validate
        if (!validate_lengthRequired($name)) $write = false;
        if (!validate_lengthRequired($email)) $write = false;
        if (!validate_lengthRequired($bauart)) $write = false;
        if (!validate_lengthRequired($kraftstoff)) $write = false;
        if (!validate_lengthRequired($color)) $write = false;
        if (!validate_lengthRequired($tank)) $write = false;

        // 3. validate email
        if (!validate_email($email) || validate_blacklistedDomain($email)) {
            $write = false;
        }

        // ! further validations (e.g. number range)
        // if ( ... ) {
        //     $write = false;
        // }

        // if insert check successful
        if ($write) {
            $query = "UPDATE " . DATA_SOURCE . " SET name='" . $name . "', bauart='" . $bauart . "', kraftstoff='" . $kraftstoff . "', color='" . $color . "', tank=" . $tank . " WHERE id=" . $id . ";";
            $con->query($query);

            apiResponse(getDataArray(), array(), array("Successfully updated Record. '" . $id . "'"), 200);
        } else {
            apiResponse(getDataArray(), array('Validation failed'), array(), 400);
        }
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
    // http_response_code($status);

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

// * Validation functions below
// * namespace: `validate_`

/**
 * validate_prepare($var: string)
 * Prepares (e.g. trims whitespaces) and escapes html code to prevent HTML in site
 */
function validate_prepare($var)
{
    $var = trim($var);
    $var = htmlspecialchars($var);

    return $var;
}

/**
 * validate_email($var: string)
 * Special validation of an email address
 * Check the Regex expression and also for valid DNS entries
 * Returns true if valid
 */
function validate_email($var)
{
    $emailParts = explode('@', $var);

    // general Regex check
    if (filter_var($var, FILTER_VALIDATE_EMAIL)) {
        // further check for valid MX Record
        return (checkdnsrr($emailParts[1], 'MX'));
    } else {
        return false;
    }
}

/**
 * validate_lengthRequired($var: string)
 * Validate the **max length** of a **non required** argument
 * Returns true if valid
 */
function validate_lengthRequired($var)
{
    return (strlen($var) > 0 && strlen($var) <= 255);
}

/**
 * validate_length($var: string)
 * Validate the **max length** of a **non required** argument
 * Returns true if valid
 */
function validate_length($var)
{
    return (strlen($var) <= 255);
}

/**
 * validate_tempMailCheck($var: string)
 * Returns **true** if the mail is in a well known temp mail database
 */
function validate_blacklistedDomain($email)
{
    // get records from file (local server)
    $xmlContent = file_get_contents('trashmails.xml');

    // initialize helpers
    $xml = new SimpleXMLElement($xmlContent);
    $domains = array();

    // for every domain write into php array
    foreach ($xml->domainitem as $row) {
        array_push($domains, $row->domain);
    }

    // return result
    return in_array(explode('@', $email)[1], $domains);
}
