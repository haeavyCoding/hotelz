<?php
session_start();

$host = 'localhost';   // Replace with your host
$user = 'u995505515_admin2';        // Replace with your database username
$password = 'Delhi@16789';        // Replace with your database password
$dbname = 'u995505515_admin2';     // Database name

$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the current counts from the database (assuming you have only one row in the count table)
$sql = "SELECT * FROM count WHERE id = 1"; // Assuming your count table has one record with id = 1
$result = $conn->query($sql);
$row = $result->fetch_assoc();

if (!$row) {
    // If no records exist, initialize the table with default values
    $sql = "INSERT INTO count (visit, google, custom, inactive) VALUES (0, 0, 0, 0)";
    $conn->query($sql);
    $row = ['visit' => 0, 'google' => 0, 'custom' => 0, 'inactive' => 0];
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'visit':
        $row['visit']++;
        $sql = "UPDATE count SET visit = {$row['visit']} WHERE id = 1";
        $conn->query($sql);
        break;
    case 'google':
        $row['google']++;
        $sql = "UPDATE count SET google = {$row['google']} WHERE id = 1";
        $conn->query($sql);
        break;
    case 'custom':
        $row['custom']++;
        $sql = "UPDATE count SET custom = {$row['custom']} WHERE id = 1";
        $conn->query($sql);
        break;
    case 'inactive':
        $row['inactive']++;
        $sql = "UPDATE count SET inactive = {$row['inactive']} WHERE id = 1";
        $conn->query($sql);
        break;
}

$conn->close();

header('Content-Type: application/json');
echo json_encode(['status' => 'success']);
?>
