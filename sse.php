<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

require_once 'config.php';

// Check admin authentication
session_start();
if (!isset($_SESSION['user'])) {
    die();
}

$lastEventId = $_SERVER['HTTP_LAST_EVENT_ID'] ?? 0;

while (true) {
    $stmt = $conn->prepare("
        SELECT n.*, h.hotel_name
        FROM notifications n
        JOIN hotels h ON n.hotel_id = h.id
        WHERE n.id > ?
        ORDER BY created_at DESC
        LIMIT 1
    ");
    $stmt->bind_param("i", $lastEventId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result) {
        echo "data: " . json_encode($result) . "\n\n";
        ob_flush();
        flush();
        $lastEventId = $result['id'];
    }

    sleep(1); // Check every 1 second
}
