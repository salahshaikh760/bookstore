<?php
// LIVE SERVER CREDENTIALS
$servername = "sql202.infinityfree.com"; 
$username = "if0_40933134";
$password = "Bookstore2026";  // <--- Ensure this matches what you set in the dashboard
$dbname = "if0_40933134_epiz_12345_bookstore";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>