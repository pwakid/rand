<?php
error_reporting(E_ALL);
ini_set('display_errors', 'on');

// Assuming $conn is a valid MySQLi connection instance created outside of this function
global $conn;

function logError($errno, $errstr, $errfile, $errline) {
    global $conn;

    // Assuming these can be determined at runtime. Adjust as necessary for your application.
    $userId = isset($_SESSION['userId']) ? $_SESSION['userId'] : NULL;
    $ipAddress = $_SERVER['REMOTE_ADDR'];

    // Prepare SQL query to insert error log into the database
    if ($stmt = $conn->prepare("INSERT INTO error_logs (error_message, error_severity, error_file, error_line, user_id, ip_address) VALUES (?, ?, ?, ?, ?, ?)")) {

        // Bind parameters to the prepared statement
        $stmt->bind_param("ssssis", $errstr, $errno, $errfile, $errline, $userId, $ipAddress);

        if (!$stmt->execute()) {
            // Log to file if statement execution fails
            error_log("Error executing statement: " . $stmt->error, 3, "/path/to/your-error-log.log");
        }

        $stmt->close();
    } else {
        // Log to file if statement preparation fails
        error_log("Error preparing statement: " . $conn->error, 3, "/path/to/your-error-log.log");
    }

    // Log all errors to file for audit
    $logMessage = sprintf("[%s] Error: %s, Severity: %s, File: %s, Line: %s, User ID: %s, IP Address: %s\n",
                          date("Y-m-d H:i:s"), $errstr, $errno, $errfile, $errline, $userId, $ipAddress);
    error_log($logMessage, 3, "/path/to/your-error-log.log");
}

?>
