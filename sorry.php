<?php
$reasons = [
    'invalid_request' => 'Invalid request - no hotel specified',
    'invalid_id' => 'Invalid hotel ID',
    'not_found_or_disabled' => 'Hotel not found or landing page disabled'
];

$reason = $_GET['reason'] ?? 'not_found_or_disabled';
$message = $reasons[$reason] ?? 'The requested hotel landing page is currently undergoing maintenance.';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>We'll Be Back Soon</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f4f8;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 500px;
        }
        .icon {
            font-size: 48px;
            color: #ffc107;
            margin-bottom: 20px;
        }
        h1 {
            font-size: 28px;
            color: #343a40;
            margin-bottom: 10px;
        }
        p {
            color: #6c757d;
            font-size: 16px;
            margin-bottom: 20px;
        }
        .btn {
            background-color: #007bff;
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 5px;
            transition: background 0.3s ease;
        }
        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🛠️</div>
        <h1>We're Doing Some Maintenance</h1>
        <p><?php echo htmlspecialchars($message); ?></p>
        <p>We're working hard to bring things back as soon as possible. Please check back later or contact the hotel directly for urgent inquiries.</p>
    </div>
</body>
</html>
