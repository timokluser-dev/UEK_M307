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
define('MYSQL_DB', 'm307_timo');
// SQL Table
define('DATA_SOURCE', 'timo_apps');

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
            // * rating is new int because if HTML Slider
            $schema = "CREATE TABLE IF NOT EXISTS timo_apps (
                app_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                app_name VARCHAR(255) NOT NULL,
                app_kaufdatum DATE NOT NULL,
                app_kaufpreis DECIMAL(5,2) NULL,
                app_kategorie VARCHAR(255) NOT NULL,
                app_rating INT NULL
            );";
            $con->query($schema);


            // * insert Data
            // * ALWAYS **3 Rows** as example

            $data = "INSERT INTO timo_apps(app_name,app_kaufdatum,app_kaufpreis,app_kategorie,app_rating) VALUES
                ('Todos!','2016-12-01',15.50,'Work',5),
                ('Slash\'n Go','2017-02-01',12.00,'Games',4),
                ('Uzmosy','2017-03-01',10.50,'Social',4);";
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
        $query = "SELECT * FROM " . DATA_SOURCE . " WHERE app_id =" . $id . ";";
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
    $app_name = @$_POST['app_name']; // *
    $app_kaufdatum = @$_POST['app_kaufdatum']; // *
    $app_kaufpreis = @$_POST['app_kaufpreis'];
    $app_kategorie = @$_POST['app_kategorie']; // *
    $app_rating = @$_POST['app_rating'];

    // check for validity (e.g. was sent via POST) - only required fields
    if (isset($app_name) && isset($app_kaufdatum) && isset($app_kategorie)) {
        // 1. prepare
        $app_name = validate_prepare($app_name);
        $app_kaufdatum = validate_prepare($app_kaufdatum);
        $app_kaufpreis = validate_prepare($app_kaufpreis);
        $app_kategorie = validate_prepare($app_kategorie);
        $app_rating = validate_prepare($app_rating);

        // 2. validate
        if (!validate_lengthRequired($app_name)) $write = false;
        if (!validate_lengthRequired($app_kaufdatum)) $write = false;
        // if (!validate_length($app_kaufpreis)) $write = false;
        if (!validate_lengthRequired($app_kategorie)) $write = false;
        // if (!validate_length($app_rating)) $write = false;

        // // 3. validate email
        // if (!validate_email($email) || validate_blacklistedDomain($email)) {
        //     $write = false;
        // }

        // ! further validations (e.g. number range)
        // optional fields set to null
        if (empty($app_rating)) {
            $app_rating = 'NULL';
        } else {
            // check rating valid
            if ($app_rating <= 6 && $app_rating >= 1) {
                // if decimal
                if ($app_rating % 1 != 0) {
                    $write = false;
                }
            } else {
                $write = false;
            }
        }

        // optional kaufpreis
        if ($app_kaufpreis == 0) {
            // type conversion because of PHP
            $app_kaufpreis = 0.0;
        } else if (empty($app_kaufpreis)) {
            $app_kaufpreis = 'NULL';
        } else {
            // check kaufpreis valid
            if (!($app_kaufpreis >= 0.0 && $app_kaufpreis <= 999.99)) {
                $write = false;
            }
        }

        // kaufdatum is valid date format?
        if (preg_match('(/^\d{4}-\d{2}-\d{2}$/)', $app_kaufdatum)) {
            $write = false;
        }

        // if insert check successful
        if ($write) {
            $query = "INSERT INTO timo_apps(app_name,app_kaufdatum,app_kaufpreis,app_kategorie,app_rating) VALUES ('" . $app_name . "','" . $app_kaufdatum . "'," . $app_kaufpreis . ",'" . $app_kategorie . "'," . $app_rating . ");";
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
    $app_name = @$_POST['app_name']; // *
    $app_kaufdatum = @$_POST['app_kaufdatum']; // *
    $app_kaufpreis = @$_POST['app_kaufpreis'];
    $app_kategorie = @$_POST['app_kategorie']; // *
    $app_rating = @$_POST['app_rating'];

    // check for validity (e.g. was sent via POST) - only required fields
    if (isset($app_name) && isset($app_kaufdatum) && isset($app_kategorie)) {
        // 1. prepare
        $app_name = validate_prepare($app_name);
        $app_kaufdatum = validate_prepare($app_kaufdatum);
        $app_kaufpreis = validate_prepare($app_kaufpreis);
        $app_kategorie = validate_prepare($app_kategorie);
        $app_rating = validate_prepare($app_rating);

        // 2. validate
        if (!validate_lengthRequired($app_name)) $write = false;
        if (!validate_lengthRequired($app_kaufdatum)) $write = false;
        // if (!validate_length($app_kaufpreis)) $write = false;
        if (!validate_lengthRequired($app_kategorie)) $write = false;
        // if (!validate_length($app_rating)) $write = false;

        // // 3. validate email
        // if (!validate_email($email) || validate_blacklistedDomain($email)) {
        //     $write = false;
        // }

        // ! further validations (e.g. number range)
        // optional fields set to null
        if (empty($app_rating)) {
            $app_rating = 'NULL';
        } else {
            // check rating valid
            if ($app_rating <= 6 && $app_rating >= 1) {
                // if decimal
                if ($app_rating % 1 != 0) {
                    $write = false;
                }
            } else {
                $write = false;
            }
        }

        // optional kaufpreis
        if ($app_kaufpreis == 0) {
            // type conversion because of PHP
            $app_kaufpreis = 0.0;
        } else if (empty($app_kaufpreis)) {
            $app_kaufpreis = 'NULL';
        } else {
            // check kaufpreis valid
            if (!($app_kaufpreis >= 0.0 && $app_kaufpreis <= 999.99)) {
                $write = false;
            }
        }

        // kaufdatum is valid date format?
        if (preg_match('(/^\d{4}-\d{2}-\d{2}$/)', $app_kaufdatum)) {
            $write = false;
        }

        // if insert check successful
        if ($write) {
            $query = "UPDATE " . DATA_SOURCE . " SET app_name='" . $app_name . "', app_kaufdatum='" . $app_kaufdatum . "', app_kaufpreis=" . $app_kaufpreis . ", app_kategorie='" . $app_kategorie . "', app_rating=" . $app_rating . " WHERE app_id=" . $id . ";";
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
        $query = "DELETE FROM " . DATA_SOURCE . " WHERE app_id=" . $id . ";";
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

    // escape '
    $var = addslashes($var);

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
