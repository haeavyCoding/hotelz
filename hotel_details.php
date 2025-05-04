<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: auth_system/login.php');
    exit();
}
require_once 'config.php';

if (!isset($_GET['id'])) {
    header("Location: hotels.php");
    exit();
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM hotels WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: hotels.php");
    exit();
}

$hotel = $result->fetch_assoc();

// Get service clicks
$serviceClicks = [];
$clickStmt = $conn->prepare("SELECT service_name, click_count FROM service_clicks WHERE hotel_id = ? ORDER BY click_count DESC");
$clickStmt->bind_param("i", $id);
$clickStmt->execute();
$clickResult = $clickStmt->get_result();

while ($row = $clickResult->fetch_assoc()) {
    $serviceClicks[$row['service_name']] = $row['click_count'];
}
// Assuming $conn is your active MySQLi connection and $id is sanitized or coming from a safe source
$rating = "SELECT overall_rating FROM reviews WHERE hotel_id = ?";
$stmt = $conn->prepare($rating);
$stmt->bind_param("i", $id); // 'i' for integer
$stmt->execute();
$result = $stmt->get_result();

$sum = 0;
$count = 0;

while ($row = $result->fetch_assoc()) {
    $sum += $row['overall_rating'];
    $count++;
}

$average_rating = ($count > 0) ? round($sum / $count, 2) : "No ratings yet";



include 'layouts/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($hotel['hotel_name']); ?> - Luxury Stays</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --gold-color: #FFD700;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --text-color: #495057;
            --border-radius: 12px;
            --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            color: var(--text-color);
            line-height: 1.6;
        }

        .container {
            width: 100% !important;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header Styles */
        .admin-header {
            background: white;
            padding: 25px 0;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .admin-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 15px;
            font-weight: 600;
        }

        .admin-header h1 i {
            color: var(--secondary-color);
            font-size: 1.8rem;
        }

        .btn-group {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            padding: 12px 25px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            gap: 10px;
            font-size: 0.95rem;
            border: none;
            cursor: pointer;
        }

        .btn i {
            font-size: 1rem;
        }

        .btn-primary {
            background: var(--secondary-color);
            color: white;
        }

        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-3px);
        }

        .btn-danger {
            background: var(--accent-color);
            color: white;
        }

        .btn-danger:hover {
            background: #c0392b;
            transform: translateY(-3px);
        }

        .btn-gold {
            background: var(--gold-color);
            color: var(--dark-color);
        }

        .btn-gold:hover {
            background: #e6c200;
            transform: translateY(-3px);
        }

        /* Hotel Details Styles */
        .hotel-details {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            margin-bottom: 40px;
        }

        .hotel-image-container {
            position: relative;
            overflow: hidden;
            height: 500px;
            width: 100%;
        }

        .hotel-image {
            height: 100%;
            width: 100%;
            object-fit: cover;
            transition: transform 0.8s ease;
        }

        .hotel-details:hover .hotel-image {
            transform: scale(1.03);
        }

        .image-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 40%;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.8), transparent);
            z-index: 1;
        }

        .hotel-title {
            position: absolute;
            bottom: 40px;
            left: 40px;
            color: white;
            z-index: 2;
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 600;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .hotel-meta {
            position: absolute;
            bottom: 40px;
            right: 40px;
            display: flex;
            gap: 15px;
            z-index: 2;
        }

        .hotel-rating, .hotel-views {
            background: rgba(255, 255, 255, 0.9);
            color: var(--primary-color);
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .hotel-rating i {
            color: var(--gold-color);
        }

        .hotel-views i {
            color: var(--secondary-color);
        }

        .hotel-info {
            padding: 40px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
        }

        .info-section {
            margin-bottom: 25px;
        }

        .info-section h3 {
            font-family: 'Playfair Display', serif;
            color: var(--primary-color);
            margin-bottom: 20px;
            font-size: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 12px;
            border-bottom: 2px solid #f0f0f0;
        }

        .info-section h3 i {
            color: var(--secondary-color);
            font-size: 1.3rem;
        }

        .info-section p {
            color: var(--text-color);
            margin: 15px 0;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 1rem;
            line-height: 1.7;
        }

        .info-section p i {
            width: 24px;
            color: var(--secondary-color);
            font-size: 1rem;
            margin-top: 3px;
        }

        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }

        .social-links a {
            color: var(--secondary-color);
            font-size: 1.4rem;
            transition: var(--transition);
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(52, 152, 219, 0.1);
        }

        .social-links a:hover {
            color: white;
            background: var(--secondary-color);
            transform: translateY(-3px);
        }

        .amenities-list {
            list-style: none;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
        }

        .amenities-list li {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text-color);
            padding: 10px 15px;
            background: #f8f9fa;
            border-radius: 8px;
            transition: var(--transition);
            font-size: 0.95rem;
        }

        .amenities-list li:hover {
            background: var(--secondary-color);
            color: white;
            transform: translateX(5px);
        }

        .amenities-list li i {
            color: var(--accent-color);
            transition: var(--transition);
        }

        .amenities-list li:hover i {
            color: white;
        }

        .content-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            line-height: 1.8;
            font-size: 0.95rem;
            border-left: 4px solid var(--secondary-color);
        }

        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            padding: 0 40px 40px;
            justify-content: center;
        }

        /* Breadcrumb */
        .breadcrumb {
            padding: 15px 0;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .breadcrumb a {
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .breadcrumb i {
            margin: 0 8px;
            color: #999;
        }

        /* Stats Section */
        .stats-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: var(--border-radius);
            margin: 30px 40px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 5px;
        }

        .stat-label {
            color: var(--text-color);
            font-size: 0.9rem;
        }

        /* Service Popularity */
        .popular-services {
            margin-top: 20px;
        }

        .service-table {
            width: 100%;
            border-collapse: collapse;
        }

        .service-table th, .service-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .service-table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .progress {
            height: 20px;
            background-color: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background-color: var(--secondary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.75rem;
            font-weight: 600;
        }

        /* Panel Button */
        .panel-btn {
            display: inline-flex;
            align-items: center;
            padding: 10px 20px;
            background: var(--primary-color);
            color: white;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            margin-left: 15px;
        }

        .panel-btn:hover {
            background: #1a252f;
            transform: translateY(-2px);
        }

        /* Responsive Styles */
        @media (max-width: 992px) {
            .hotel-image-container {
                height: 400px;
            }
            
            .hotel-title {
                font-size: 2rem;
                bottom: 30px;
                left: 30px;
            }
            
            .hotel-meta {
                bottom: 30px;
                right: 30px;
            }
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .hotel-image-container {
                height: 350px;
            }
            
            .hotel-info {
                padding: 30px;
                grid-template-columns: 1fr;
            }
            
            .hotel-title {
                font-size: 1.8rem;
                bottom: 25px;
                left: 25px;
            }
            
            .hotel-meta {
                flex-direction: column;
                gap: 10px;
                bottom: 25px;
                right: 25px;
            }
            
            .action-buttons {
                padding: 0 30px 30px;
            }
        }

        @media (max-width: 576px) {
            .hotel-image-container {
                height: 250px;
            }
            
            .hotel-title {
                font-size: 1.5rem;
                bottom: 20px;
                left: 20px;
            }
            
            .hotel-rating, .hotel-views {
                padding: 8px 15px;
                font-size: 0.9rem;
            }
            
            .info-section h3 {
                font-size: 1.3rem;
            }
            
            .amenities-list {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
<main class="app-main">
    <div class="container-fluid">
        <!-- Breadcrumb Navigation -->
        <div class="breadcrumb">
            <a href="hotels.php"><i class="fas fa-chevron-left"></i> All Hotels</a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo htmlspecialchars($hotel['hotel_name']); ?></span>
        </div>

        <!-- Header -->
        <header class="admin-header">
            <div class="container header-content">
                <div class="d-flex align-items-center">
                    <h1><i class="fas fa-hotel"></i> <?php echo htmlspecialchars($hotel['hotel_name']); ?></h1>
                    <a href="hotel_panel/index.php?id=<?php echo $id ?>" class="panel-btn">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </div>
                <div class="btn-group">
                    <a href="add_hotels.php?edit=<?php echo $hotel['id']; ?>" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="delete_hotel.php?id=<?php echo $hotel['id']; ?>" class="btn btn-danger"
                        onclick="return confirm('Are you sure you want to delete this hotel?')">
                        <i class="fas fa-trash"></i> Delete
                    </a>
                    <a href="hotels.php" class="btn btn-gold">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </header>

        <!-- Hotel Details -->
        <div class="hotel-details">
            <?php if ($hotel['image_url']): ?>
                <div class="hotel-image-container">
                    <img src="<?php echo htmlspecialchars($hotel['image_url']); ?>"
                        alt="<?php echo htmlspecialchars($hotel['hotel_name']); ?>" class="hotel-image">
                    <div class="image-overlay"></div>
                    <h2 class="hotel-title"><?php echo htmlspecialchars($hotel['hotel_name']); ?></h2>
                    <div class="hotel-meta">
                        <div class="hotel-rating">
                            <i class="fas fa-star"></i>
                            <?php echo "$average_rating"; ?>
                        </div>
                        <div class="hotel-views">
                            <i class="fas fa-eye"></i>
                            <?php echo number_format($hotel['visit_count'] ?? 0); ?> views
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Stats Section -->
            <div class="stats-section">
                <h3><i class="fas fa-chart-bar"></i> Hotel Statistics</h3>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value"><?php echo number_format($hotel['visit_count'] ?? 0); ?></div>
                        <div class="stat-label">Total Views</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo number_format(array_sum($serviceClicks)); ?></div>
                        <div class="stat-label">Service Clicks</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo count($serviceClicks); ?></div>
                        <div class="stat-label">Services Tracked</div>
                    </div>
                </div>
            </div>

            <div class="hotel-info">
                <div class="info-section">
                    <h3><i class="fas fa-info-circle"></i> Basic Information</h3>
                    <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($hotel['location']); ?></p>
                    <?php if ($hotel['price_range']): ?>
                        <p><i class="fas fa-tag"></i> Price Range: <?php echo htmlspecialchars($hotel['price_range']); ?></p>
                    <?php endif; ?>
                    <?php if ($hotel['description']): ?>
                        <div class="content-box">
                            <?php echo nl2br(htmlspecialchars($hotel['description'])); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="info-section">
                    <h3><i class="fas fa-phone-alt"></i> Contact Information</h3>
                    <?php if ($hotel['phone']): ?>
                        <p><i class="fas fa-phone"></i> Phone: <?php echo htmlspecialchars($hotel['phone']); ?></p>
                    <?php endif; ?>
                    <?php if ($hotel['whatsapp']): ?>
                        <p><i class="fab fa-whatsapp"></i> WhatsApp: <?php echo htmlspecialchars($hotel['whatsapp']); ?></p>
                    <?php endif; ?>
                    <?php if ($hotel['email']): ?>
                        <p><i class="fas fa-envelope"></i> Email: <?php echo htmlspecialchars($hotel['email']); ?></p>
                    <?php endif; ?>

                    <div class="social-links">
                        <?php if ($hotel['google_review_link']): ?>
                            <a href="<?php echo htmlspecialchars($hotel['google_review_link']); ?>" target="_blank">
                                <i class="fab fa-google"></i>
                            </a>
                        <?php endif; ?>
                        <?php if ($hotel['facebook_link']): ?>
                            <a href="<?php echo htmlspecialchars($hotel['facebook_link']); ?>" target="_blank">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                        <?php endif; ?>
                        <?php if ($hotel['instagram_link']): ?>
                            <a href="<?php echo htmlspecialchars($hotel['instagram_link']); ?>" target="_blank">
                                <i class="fab fa-instagram"></i>
                            </a>
                        <?php endif; ?>
                        <?php if ($hotel['website']): ?>
                            <a href="<?php echo htmlspecialchars($hotel['website']); ?>" target="_blank">
                                <i class="fas fa-globe"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if ($hotel['amenities']): ?>
                <div class="info-section">
                    <h3><i class="fas fa-concierge-bell"></i> Amenities & Services</h3>
                    <ul class="amenities-list">
                        <?php foreach (explode(',', $hotel['amenities']) as $amenity): ?>
                            <li><i class="fas fa-check"></i> <?php echo htmlspecialchars(trim($amenity)); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($hotel['dining_menu']): ?>
                <div class="info-section">
                    <h3><i class="fas fa-utensils"></i> Dining & Cuisine</h3>
                    <div class="content-box">
                        <?php echo nl2br(htmlspecialchars($hotel['dining_menu'])); ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Location Map -->
            <div class="info-section">
                <h3><i class="fas fa-map-marked-alt"></i> Location</h3>
                <div style="height: 400px; width: 100%; border-radius: 8px; overflow: hidden;">
                    <iframe width="100%" height="100%" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"
                        src="https://maps.google.com/maps?q=<?php echo urlencode($hotel['location']); ?>&output=embed">
                    </iframe>
                </div>
            </div>

            <!-- Service Popularity -->
            <div class="info-section">
                <h3><i class="fas fa-chart-line"></i> Service Popularity</h3>
                <?php if (!empty($serviceClicks)): ?>
                    <div class="popular-services">
                        <table class="service-table">
                            <thead>
                                <tr>
                                    <th>Service</th>
                                    <th>Clicks</th>
                                    <th>Popularity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $maxClicks = max($serviceClicks);
                                foreach ($serviceClicks as $service => $clicks): 
                                    $percentage = $maxClicks > 0 ? ($clicks / $maxClicks) * 100 : 0;
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($service); ?></td>
                                        <td><?php echo $clicks; ?></td>
                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar" style="width: <?php echo $percentage; ?>%">
                                                    <?php echo round($percentage); ?>%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>No service click data available yet.</p>
                <?php endif; ?>
            </div>

            <div class="action-buttons">
                <a href="add_hotels.php?edit=<?php echo $hotel['id']; ?>" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit Hotel
                </a>
                <a href="delete_hotel.php?id=<?php echo $hotel['id']; ?>" class="btn btn-danger"
                    onclick="return confirm('Are you sure you want to delete this hotel?')">
                    <i class="fas fa-trash"></i> Delete Hotel
                </a>
                <a href="create_clients_id_pass.php?id=<?php echo $hotel['id']; ?>" class="btn btn-primary">
                    <i class="fas fa-id-card"></i> Create ID/Pass
                </a>
                <a href="hotels.php" class="btn btn-gold">
                    <i class="fas fa-list"></i> All Hotels
                </a>
            </div>
        </div>
    </div>
</main>
<?php include 'layouts/footer.php'; ?>