<?php
// Database configuration
$servername = "localhost";
$username = "u995505515_admin2";
$password = "Delhi@16789";
$dbname = "u995505515_admin2";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve data from POST request
$overallRating = $_POST['overallRating'];
$roomsRating = $_POST['roomsRating'];
$serviceRating = $_POST['serviceRating'];
$locationRating = $_POST['locationRating'];
// $userName = $_POST['userName'];
// $contactNumber = $_POST['contactNumber'];
$experience = $_POST['experience'];
$tripType = $_POST['tripType'];
$travelWith = $_POST['travelWith'];
$descriptions = json_decode($_POST['descriptions'], true);
$topics = json_decode($_POST['topics'], true);

// Convert arrays to JSON strings and store them in variables
$descriptionsJson = json_encode($descriptions);
$topicsJson = json_encode($topics);

// Prepare SQL statement
$stmt = $conn->prepare("INSERT INTO reviews (overall_rating, rooms_rating, service_rating, location_rating, experience, trip_type, travel_with, descriptions, topics) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iiiisssss", $overallRating, $roomsRating, $serviceRating, $locationRating, $experience, $tripType, $travelWith, $descriptionsJson, $topicsJson);

if ($stmt->execute()) {
    echo "Thank You..! Your Feedback is Valuable to Us";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();

// Handle file uploads (photos & videos)
$mediaDirectory = "uploads/";
if (!file_exists($mediaDirectory)) {
    mkdir($mediaDirectory, 0777, true);
}

// Check if the media key exists in the $_FILES array
if (isset($_FILES['media']) && is_array($_FILES['media']['tmp_name'])) {
    foreach ($_FILES['media']['tmp_name'] as $key => $tmpName) {
        $fileName = basename($_FILES['media']['name'][$key]);
        $targetFilePath = $mediaDirectory . $fileName;

        // Move the uploaded file to the target directory
        if (move_uploaded_file($tmpName, $targetFilePath)) {
            // Save file path to the database if needed,   For example: 
            $conn->query("INSERT INTO media (review_id, file_path) VALUES (LAST_INSERT_ID(), '$targetFilePath')");
        } else {
            echo "Error uploading file: " . $fileName . "<br />";
        }
    }
} else {
    // echo "No media files uploaded.<br />";
}

$conn->close();
