<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'crm_system');
define('DB_USER', 'root');
define('DB_PASS', '');

// Create a MySQL connection
$conn = mysql_connect(DB_HOST, DB_USER, DB_PASS);
if (!$conn) {
    die("Database connection failed: " . mysql_error());
}

// Select database
if (!mysql_select_db(DB_NAME, $conn)) {
    die("Database selection failed: " . mysql_error());
}

// Set charset to utf8
mysql_set_charset('utf8', $conn);
?>
