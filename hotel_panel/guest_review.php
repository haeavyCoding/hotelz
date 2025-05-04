<?php
include '../config.php'; // DB connection
if (!isset($_SESSION['is_logedin']) || $_SESSION['is_logedin'] !== true) {
    header('Location: login.php');
    exit();
}
// Sanitize the input to prevent SQL injection
$id = $_SESSION['hotel_id'];
$id = $_GET['review_id'];
$sql = "SELECT * FROM reviews WHERE id = '$id' LIMIT 1";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Guest Review - <?php echo htmlspecialchars($row['guest_name']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f0f2f5;
            margin: 0;
            padding: 30px;
        }

        .container {
            max-width: 900px;
            margin: auto;
            background: #ffffff;
            padding: 35px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .header {
            border-bottom: 1px solid #eee;
            margin-bottom: 25px;
        }

        .header h2 {
            margin: 0;
            font-size: 26px;
            color: #2c3e50;
        }

        .meta {
            font-size: 14px;
            color: #888;
            margin-top: 5px;
        }

        .ratings {
            display: flex;
            gap: 20px;
            margin: 20px 0;
            flex-wrap: wrap;
        }

        .rating-card {
            background: #f7f9fb;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            color: #2d3436;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        }

        .section {
            margin: 20px 0;
        }

        .section p {
            margin: 8px 0;
            line-height: 1.6;
        }

        .label {
            font-weight: 600;
            color: #333;
        }

        .stars {
            color: #f1c40f;
            margin-left: 5px;
        }

        .media img {
            width: 120px;
            height: auto;
            margin: 5px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
            transition: transform 0.3s;
        }

        .media img:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>
<div class="container">
    <?php if ($row): ?>
        <div class="header">
            <h2><?php echo htmlspecialchars($row['guest_name']); ?></h2>
            <div class="meta">Trip Type: <?php echo $row['trip_type']; ?> | Reviewed on: <?php echo $row['created_at']; ?></div>
        </div>

        <div class="ratings">
            <div class="rating-card">Overall: <?php echo $row['overall_rating']; ?> 
                <span class="stars"><?php echo str_repeat('<i class="fas fa-star"></i>', round($row['overall_rating'])); ?></span>
            </div>
            <div class="rating-card">Room: <?php echo $row['rooms_rating']; ?>/5</div>
            <div class="rating-card">Service: <?php echo $row['service_rating']; ?>/5</div>
            <div class="rating-card">Location: <?php echo $row['location_rating']; ?>/5</div>
        </div>

        <div class="section">
            <p><span class="label">Stayed with:</span> <?php echo $row['travel_with']; ?></p>
            <p><span class="label">Hotel Description:</span> <?php echo $row['hotel_description']; ?></p>
            <p><span class="label">Experience:</span> <?php echo $row['experience']; ?></p>
            <p><span class="label">Topics Mentioned:</span> <?php echo $row['topics']; ?></p>
        </div>

        <?php if (!empty($row['media'])): ?>
            <div class="section media">
                <p><span class="label">Photos:</span></p>
                <?php
                $images = explode(',', $row['media']);
                foreach ($images as $img) {
                    echo "<img src='uploads/" . trim($img) . "' alt='Review Image'>";
                }
                ?>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <p style="color: red; text-align: center;">No review found.</p>
    <?php endif; ?>
</div>
</body>
</html>
