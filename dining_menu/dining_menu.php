<?php
include_once('../config.php');

// Get hotel ID from URL
$hotel_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Query the hotel
$sql = "SELECT * FROM hotels WHERE id = $hotel_id";
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) > 0) {
    $hotel = mysqli_fetch_assoc($result);

} else {
    echo "<div class='alert alert-danger text-center'>Hotel not found.</div>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dining Menu</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar {
            background-color: #28a745;
        }

        .navbar-brand {
            font-weight: bold;
            color: white;
        }

        .navbar-brand:hover {
            color: #e9ecef;
        }

        .main-content {
            flex: 1;
            padding: 30px 20px;
        }

        .hotel-title {
            font-size: 36px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 40px;
            color: #28a745;
        }

        .image-wrapper {
            margin-bottom: 30px;
        }

        .media-item {
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .media-item:hover {
            transform: scale(1.01);
        }

        .media-height {
            min-height: 80vh;
        }

        .footer {
            background-color: #28a745;
            color: white;
            padding: 20px 0;
            text-align: center;
        }

        @media (max-width: 768px) {
            .hotel-title {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>

<!-- Header / Navbar -->
<nav class="navbar navbar-expand-lg">
    <div class="container">
       <a class="navbar-brand" href="#">
    <?php 
    echo !empty($hotel['logo_of_hotel']) 
        ? '<img height="100" src="../' . htmlspecialchars($hotel['logo_of_hotel']) . '" alt="Hotel Logo">' 
        : htmlspecialchars($hotel['hotel_name']); 
    ?>
</a>

    </div>
</nav>

<!-- Main Content -->
<div class="main-content container">

<?php
if (mysqli_num_rows($result) > 0) {
    echo "<div class='hotel-title'>{$hotel['hotel_name']}</div>";

    $files = explode(',', $hotel['dining_menu']);

    echo "<div class='row'>";
    foreach ($files as $file) {
        $file = trim($file);
        if (!empty($file)) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            echo "<div class='col-lg-12 col-md-12 image-wrapper'>";
            if ($ext === 'pdf') {
                echo "<iframe src='../$file' class='media-height w-100' frameborder='0'></iframe>";
            } else {
                echo "<img src='../$file' alt='Dining Menu' class='media-item'>";
            }
            echo "</div>";
        }
    }
    echo "</div>";
} else {
    echo "<div class='alert alert-danger text-center'>Hotel not found.</div>";
}
?>

</div>

<!-- Footer -->
<footer class="footer">
    <div class="container">
        &copy; <?php echo date('Y'); ?> My Hotel. All rights reserved.
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
