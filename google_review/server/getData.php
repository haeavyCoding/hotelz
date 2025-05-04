<?php
// Database connection
$host = 'localhost';   // Replace with your host
$user = 'u995505515_admin2';        // Replace with your database username
$password = 'Delhi@16789';        // Replace with your database password
$dbname = 'u995505515_admin2';     // Database name

$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the counts from the database
$sql = "SELECT visit, google, custom, inactive FROM count WHERE id = 1"; // Assuming your count table has one record with id = 1
$result = $conn->query($sql);

$data = ['visit' => 0, 'google' => 0, 'custom' => 0, 'inactive' => 0];

if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
}

$conn->close();

// Return the data as JSON
header('Content-Type: application/json');
echo json_encode($data);
?>
