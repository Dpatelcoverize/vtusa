<?php
/* Database credentials. Assuming you are running MySQL
server with default setting (user 'root' with no password) */
//define('DB_SERVER', 'mysql.chapar54.dreamhosters.com');
// define('DB_SERVER', '64.90.32.97');
// define('DB_USERNAME', 'vtcrm_user');
// define('DB_PASSWORD', 'S11mple42S11mon');
// define('DB_NAME', 'vt_crmdb');

// define('DB_SERVER', 'localhost');
// define('DB_USERNAME', 'root');
// define('DB_PASSWORD', '');
// define('DB_NAME', 'vital_live');
require_once __DIR__ . '/../config.php';
// require_once '../log-exception.php';

/* Attempt to connect to MySQL database */
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($link === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

function logException($e, $errorType = 'Exception')
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    // Use the existing $link connection if available, otherwise create a new one
    global $link;

    if (!$link) {
        $link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
        if (!$link) {
            error_log("DB connection failed: " . mysqli_connect_error());
            return;
        }
    }

    // var_dump($_SESSION);die();
    $admin_username = '';
    $admin_id = 0;
    $user_name = "";
    $user_id = 0;
    $role = "";

    if (isset($_SESSION['admin_loggedin'], $_SESSION['loggedin']) && $_SESSION['admin_loggedin'] == true && $_SESSION['loggedin'] == true) {
        $admin_username = $_SESSION['admin_username'];
        $admin_id = $_SESSION['admin_id'];
        $user_name = $_SESSION['username'];
        $user_id = $_SESSION['id'];
        $role = $_SESSION['userType'];
    } else if (isset($_SESSION['admin_loggedin']) && $_SESSION['admin_loggedin'] == true) {
        $admin_username = $_SESSION['admin_username'];
        $admin_id = $_SESSION['admin_id'];
    } else if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
        $user_name = $_SESSION['username'];
        $user_id = $_SESSION['id'];
        $role = $_SESSION['userType'];
    }

    $message = mysqli_real_escape_string($link, $e->getMessage());
    $file = mysqli_real_escape_string($link, $e->getFile());
    $line = $e->getLine();
    $trace = mysqli_real_escape_string($link, $e->getTraceAsString());
    $errorType = mysqli_real_escape_string($link, $errorType);


    $sql = "INSERT INTO Exception_Logs (admin_username, admin_id, user_name, user_id, role, line, error_type, message, file, trace, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = mysqli_prepare($link, $sql);
    if ($stmt) {

        mysqli_stmt_bind_param($stmt, "sisisissss", $admin_username,  $admin_id, $user_name, $user_id, $role, $line, $errorType, $message, $file, $trace);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        error_log("Failed to prepare statement: " . mysqli_error($link));
    }
    // session_abort();
}

set_exception_handler(function ($e) {
    logException($e, 'Uncaught Exception');
    echo $e;
});

// Handle warnings, notices, etc. as exceptions
set_error_handler(function ($severity, $message, $file, $line) {
    // Log manually without throwing if you don't want to stop execution
    $e = new ErrorException($message, 0, $severity, $file, $line);
    logException($e, 'Runtime Error');
    // Optional: throw if you want it to stop execution
    // throw $e;
});

// Handle fatal shutdown errors
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
        $e = new ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
        logException($e, 'Fatal Error');
    }
});
