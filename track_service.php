<?php
require_once 'config.php';

if (isset($_GET['hotel_id']) && isset($_GET['service']) && isset($_GET['track'])) {
    $hotelId = (int)$_GET['hotel_id'];
    $serviceName = $_GET['service'];
    $redirectUrl = $_GET['track'];
    
    try {
        // Check if record exists
        $checkStmt = $conn->prepare("SELECT id FROM service_clicks WHERE hotel_id = ? AND service_name = ?");
        $checkStmt->bind_param("is", $hotelId, $serviceName);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update existing record
            $updateStmt = $conn->prepare("UPDATE service_clicks SET click_count = click_count + 1 WHERE hotel_id = ? AND service_name = ?");
            $updateStmt->bind_param("is", $hotelId, $serviceName);
            $updateStmt->execute();
        } else {
            // Insert new record
            $insertStmt = $conn->prepare("INSERT INTO service_clicks (hotel_id, service_name, click_count) VALUES (?, ?, 1)");
            $insertStmt->bind_param("is", $hotelId, $serviceName);
            $insertStmt->execute();
        }
        
        // Redirect to the actual service URL
        header("Location: " . $redirectUrl);
        exit();
        
    } catch (Exception $e) {
        // Log error but still redirect
        error_log("Error tracking service click: " . $e->getMessage());
        header("Location: " . $redirectUrl);
        exit();
    }
} else {
    // Invalid request
    header("HTTP/1.1 400 Bad Request");
    echo "Invalid request parameters";
    exit();
}
?>