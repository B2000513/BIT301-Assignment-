<?php
$host = 'localhost';  // Host where the database is running (usually 'localhost' for local development)
$db = 'assignment301';  // The name of the database
$user = 'root';  // The database username (default for local MySQL is often 'root')
$pass = '';  // The password for the database user (leave empty for default local MySQL setup)

// Create connection to the database
$conn = new mysqli($host, $user, $pass, $db);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);  // Output error if connection fails
}
?>