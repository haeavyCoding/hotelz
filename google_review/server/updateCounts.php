<?php
session_start();

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'google':
        $_SESSION['googleReviewUsers'] = ($_SESSION['googleReviewUsers'] ?? 0) + 1;
        break;
    case 'custom':
        $_SESSION['customLinkUsers'] = ($_SESSION['customLinkUsers'] ?? 0) + 1;
        break;
    case 'inactive':
        $_SESSION['inactiveUsers'] = ($_SESSION['inactiveUsers'] ?? 0) + 1;
        break;
}

$_SESSION['totalUsers'] = ($_SESSION['totalUsers'] ?? 0) + 1;

header('Content-Type: application/json');
echo json_encode(['status' => 'success']);
?>
