<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ecommerce";

// Connect to database
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    exit('Unable to connect to the store database. Please try again shortly.');
}
$conn->set_charset('utf8mb4');
?>
