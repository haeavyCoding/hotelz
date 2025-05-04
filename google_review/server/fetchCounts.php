<?php
session_start();

$response = [
    'totalUsers' => $_SESSION['totalUsers'] ?? 0,
    'googleReviewUsers' => $_SESSION['googleReviewUsers'] ?? 0,
    'customLinkUsers' => $_SESSION['customLinkUsers'] ?? 0,
    'inactiveUsers' => $_SESSION['inactiveUsers'] ?? 0,
];

header('Content-Type: application/json');
echo json_encode($response);
?>
