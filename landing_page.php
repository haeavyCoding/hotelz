<?php
require_once 'config.php';
// Check if ID exists and is valid
if (!isset($_GET['id'])) {
    header("Location: sorry.php?reason=invalid_request");
    exit();
}

$hotelId = (int)$_GET['id'];
if ($hotelId <= 0) {
    header("Location: sorry.php?reason=invalid_id");
    exit();
}

// Check if hotel exists and landing page is enabled
$sql = "SELECT * FROM hotels WHERE id = $hotelId AND landing_page_enabled = 1";
$result = $conn->query($sql);

if (!$result || $result->num_rows === 0) {
    header("Location: sorry.php?reason=not_found_or_disabled");
    exit();
}

$id = $_GET['id'];
if (isset($_GET['id'])) {
    $hotelId = (int)$_GET['id'];
    $sql = "SELECT * FROM hotels WHERE id = $hotelId AND landing_page_enabled = 1";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $hotel = $result->fetch_assoc();
        // Display your landing page content here
    } else {
        // Hotel not found or landing page disabled
        header("HTTP/1.0 404 Not Found");
        include '404.php'; // Create a custom 404 page
        die();
    }
} else {
    header("HTTP/1.0 400 Bad Request");
    die("Invalid request");
}
// Increment visit counter first
$conn->begin_transaction();
try {
    // Get hotel data
    $stmt = $conn->prepare("SELECT * FROM hotels WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        throw new Exception("Hotel not found");
    }

    $hotel = $result->fetch_assoc();
    $conn->commit();

    // Update visit count
    $updateStmt = $conn->prepare("UPDATE hotels SET visit_count = visit_count + 1 WHERE id = ?");
    $updateStmt->bind_param("i", $id);
    $updateStmt->execute();

    // Insert notification
    $message = "New visit to " . $hotel['hotel_name'];
    $notifStmt = $conn->prepare("INSERT INTO notifications (hotel_id, message) VALUES (?, ?)");
    $notifStmt->bind_param("is", $id, $message);
    $notifStmt->execute();
    
    // Function to track service clicks
    function trackServiceClick($conn, $hotelId, $serviceName) {
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
    }
    
    // Handle service click if requested
    if (isset($_GET['track'])) {
        $serviceName = $_GET['service'] ?? '';
        if (!empty($serviceName)) {
            trackServiceClick($conn, $id, $serviceName);
            
            // Redirect to the actual service URL
            header("Location: " . $_GET['track']);
            exit();
        }
    }

} catch (Exception $e) {
    $conn->rollback();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($hotel['hotel_name']); ?> - Contactless Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
    <style>
        :root {
            --primary-color: #28a745;
            --primary-dark: #1e7e34;
            --glass-bg: rgba(255, 255, 255, 0.15);
            --glass-border: rgba(255, 255, 255, 0.2);
            --glass-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
            --text-light: rgba(255, 255, 255, 0.9);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', 'Arial', sans-serif;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            width: 100%;
            min-height: 100vh;
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
            position: relative;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        .background-image {
            width: 100%;
            height: 90vh;
            position: relative;
            overflow: hidden;
        }

        .background-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.1) 0%, rgba(0, 0, 0, 0.3) 100%);
            z-index: 1;
        }

        .background-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        .navbar {
            width: 100%;
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            position: fixed;
            top: 0;
            padding: 0 30px;
            z-index: 100;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--glass-border);
            box-shadow: var(--glass-shadow);
            padding-block: 10px;
        }

        .navbar img {
            max-width: 140px;
            height: 80px;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
            object-fit: contain;
        }

        .content-1 {
            width: 100%;
            position: absolute;
            top: 50%;
            left: 0;
            padding: 0 6%;
            transform: translateY(-50%);
            color: white;
            text-align: center;
            z-index: 2;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        .content-1 h3{
            font-size: 3rem
        }

        .buttons {
            padding: 8px 20px;
            color: white;
            background-color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            border-radius: 50px;
            display: inline-block;
            margin-bottom: 20px;
            backdrop-filter: blur(5px);
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .buttons:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            background: rgba(255, 255, 255, 0.2);
        }

        .services-container {
            width: 100%;
            padding: 40px 0 !important;
            margin-top: -150px;
            position: relative;
            z-index: 5;
        }

        .service-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--text-light);
            font-size: 16px;
            text-transform: uppercase;
            font-weight: 600;
            transition: all 0.3s ease;
            aspect-ratio: 1/1;
            text-decoration: none;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            text-align: center;
            padding: 20px;
            margin-bottom: 20px !important;
            width: 100%;
        }

        .service-item p {
            font-size: 16px !important;
        }

        .service-item:hover {
            transform: translateY(-5px) scale(1.03);
            text-shadow: 0 12px 24px rgba(0, 0, 0, 0.2);
            color: orangered;
        }

        .service-item i {
            font-size: 50px;
            margin-bottom: 20px;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
        }

        .footer {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            color: var(--text-light);
            text-align: center;
            border-top: 1px solid var(--glass-border);
            box-shadow: 0 -4px 8px rgba(0, 0, 0, 0.1);
            padding-bottom: 5px;
        }

        .footer p {
            color: gainsboro;
            font-size: 14px;
            font-weight: 400;
        }

        .footer img {
            width: 100px;
            margin-bottom: 10px;
        }

        .time-display-container {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 10px 20px;
            border-radius: 50px;
            font-family: 'Courier New', monospace;
            border: 1px solid var(--glass-border);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        #time-display {
            color: white;
            font-weight: bold;
            font-size: 1.1rem;
            letter-spacing: 2px;
        }

        /* Owl Carousel Full Width Styles */
        .owl-carousel {
            width: 100%;
            margin: 0;
        }

        .owl-carousel .owl-stage {
            display: flex;
            padding: 20px 15px;
            margin: 0 -10px;
        }

        .owl-carousel .owl-item {
            padding-left: 10px;
        }

        .owl-carousel .service-card {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(11px);
            border: 1px solid var(--glass-border);
            border-radius: 15px;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            transition: all 0.3s ease;
            margin: 0 5px;
        }

        .owl-carousel .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .owl-nav {
            position: absolute;
            top: 47%;
            width: 100%;
            display: flex;
            justify-content: space-between;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .owl-prev,
        .owl-next {
            pointer-events: auto;
            background: var(--glass-bg) !important;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            width: 50px;
            height: 50px;
            border-radius: 50% !important;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white !important;
            font-size: 24px !important;
            margin: 0 15px;
            transition: all 0.3s ease;
        }

        .owl-prev:hover,
        .owl-next:hover {
            background: rgba(255, 255, 255, 0.3) !important;
        }

        .owl-dots {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .owl-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5) !important;
            margin: 0 5px;
            transition: all 0.3s ease;
        }

        .owl-dot.active {
            background: white !important;
            transform: scale(1.3);
        }

        .disabled-link {
            pointer-events: none;
            opacity: 0.6;
            cursor: not-allowed;
            text-decoration: none;
        }

        /* Responsive Adjustments */
        @media (max-width: 992px) {
            .background-image {
                height: 90vh;
            }

            .service-item {
                font-size: 14px;
                padding: 20px;
            }

            .service-item i {
                font-size: 40px;
                margin-bottom: 15px;
            }
        }

        @media (max-width: 768px) {
            .background-image {
                height: 80vh;
            }
            .owl-nav {
            display: none
        }

            .service-item p {
                font-size: 15px !important;
            }

            .owl-prev,
            .owl-next {
                width: 30px;
                height: 30px;
                font-size: 18px !important;
            }

            .owl-nav {
                top: 44%;
            }

            .services-container {
                margin-top: -130px;
                padding: 30px 0 !important;
            }

            .service-item {
                font-size: 14px;
                padding: 15px;
                min-height: 140px;
            }

            .service-item i {
                font-size: 36px;
                margin-bottom: 12px;
            }

            .navbar {
                padding: 7px 10px;
            }
        }
        @media (max-width: 675px) {
            *{
                font-size: 12px;
            }
            .navbar img {
            width: 100px;
            height: 60px;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
            object-fit: contain;
        }

          
        }

        @media (max-width: 576px) {
            .background-image {
                height: 70vh;
                min-height: 350px;
            }
            .content-1 h3{
                font-size: 2rem;
            }

            .owl-nav {
                top: 42%;
            }

            .service-item p {
                font-size: 12px !important;
            }

            .service-item {
                font-size: 14px;
                padding: 15px;
                min-height: 120px;
                width: 100%;
            }

            .service-item i {
                font-size: 32px;
                margin-bottom: 10px;
            }

            .footer img {
                width: 80px;
            }

            .time-display-container {
                padding: 8px 15px;
            }

            #time-display {
                font-size: 1rem;
            }

            .owl-carousel .owl-stage {
                padding: 20px 10px;
            }
        }   

        @media (max-width: 400px) {
            .background-image {
                height: 70vh;
            }
            .services-container {
                margin-top: -110px;
                padding: 20px 0 !important;
            }

            .service-item {
                font-size: 12px;
                min-height: 100px;
            }

            .service-item i {
                font-size: 28px;
            }
        }
    </style>
</head>

<body>

    <div class="background-image">
        <?php
        $imagePath = $hotel['image_url'];
        if (!empty($hotel['image_url']) && file_exists($imagePath)) {
            echo "<img src='$imagePath' alt='Image'>";
        } else {
            echo "<img src='hotel-img.jpg' alt='Default Image'>";
        }
        ?>

        <div class="navbar">
            <a class="text-decoration-none" href="<?php echo $hotel['website'] ?>">
                <?php
                $logoPath = $hotel['logo_of_hotel'];
                if (!empty($logoPath) && file_exists($logoPath)) {
                    echo "<img src='$logoPath' alt='Hotel Logo'>";
                } else {
                    echo "<img src='logo2 (1).svg' alt='Default Logo'>";
                }
                ?>
            </a>
            <div class="d-flex gap-3 align-items-center">
                <div class="time-display-container">
                    <span id="time-display" class="text-white fw-bold"></span>
                </div>
            </div>
        </div>

        <div class="content-1">
            <a class="buttons">
                <i class="fas fa-hands-wash me-2"></i> Contactless Services
            </a>
            <h3 class="py-2 fw-bold">Welcome to <?php echo htmlspecialchars($hotel['hotel_name']); ?>!</h3>
            <p class="py-1 fs-5">Experience our digital hospitality services</p>
        </div>
    </div>

    <div class="services-container">
        <div class="owl-carousel owl-theme">
            <?php
            $planType = $hotel['plan_type'];

            // Basic Plan (1)
            if ($planType == 1) {
                ?>
                <!-- Basic Plan Services -->
                <div class="item">
                    <a href="track_service.php?hotel_id=<?php echo $id; ?>&service=Google Review&track=<?php echo urlencode(htmlspecialchars($hotel['google_review_link'])); ?>" class="service-item service-card">
                        <i class="fab fa-google"></i>
                        <p>Review Us</p>
                    </a>
                    <a href="track_service.php?hotel_id=<?php echo $id; ?>&service=Instagram&track=<?php echo urlencode(htmlspecialchars($hotel['instagram_link'])); ?>" class="service-item service-card">
                        <i class="fab fa-instagram"></i>
                        <p>Follow Us!</p>
                    </a>
                </div>

                <div class="item">
                    <a href="track_service.php?hotel_id=<?php echo $id; ?>&service=Facebook&track=<?php echo urlencode(htmlspecialchars($hotel['facebook_link'])); ?>" class="service-item service-card">
                        <i class="fab fa-facebook-f"></i>
                        <p>Like Us</p>
                    </a>
                    <a href="track_service.php?hotel_id=<?php echo $id; ?>&service=WhatsApp&track=<?php echo urlencode('https://wa.me/'.htmlspecialchars($hotel['whatsapp'])); ?>" class="service-item service-card">
                        <i class="fab fa-whatsapp"></i>
                        <p>Chat With Us</p>
                    </a>
                </div>

                <div class="item">
                    <a href="track_service.php?hotel_id=<?php echo $id; ?>&service=Phone Call&track=<?php echo urlencode('tel:'.htmlspecialchars($hotel['phone'])); ?>" class="service-item service-card">
                        <i class="fas fa-phone-alt"></i>
                        <p>Call Us</p>
                    </a>
                    <a href="track_service.php?hotel_id=<?php echo $id; ?>&service=Local Attractions&track=<?php echo urlencode('places/lucknow_places.php?hotel_id='.$id); ?>" class="service-item service-card">
                        <i class="fas fa-map-signs"></i>
                        <p>Local Attractions</p>
                    </a>
                </div>
                <?php
            }
            // Advanced Plan (2)
            elseif ($planType == 2) {
                ?>
                <!-- Advanced Plan Services -->
                <div class="item">
                    <a href="track_service.php?hotel_id=<?php echo $id; ?>&service=Google Review&track=<?php echo urlencode('google_review/index.php?id='.$hotel['id']); ?>" class="service-item service-card">
                        <i class="fab fa-google"></i>
                        <p>Review Us</p>
                    </a>
                    <a href="track_service.php?hotel_id=<?php echo $id; ?>&service=Facebook&track=<?php echo urlencode(htmlspecialchars($hotel['facebook_link'])); ?>" class="service-item service-card">
                        <i class="fab fa-facebook-f"></i>
                        <p>Like Us</p>
                    </a>
                </div>

                <div class="item">
                    <a href="track_service.php?hotel_id=<?php echo $id; ?>&service=Instagram&track=<?php echo urlencode(htmlspecialchars($hotel['instagram_link'])); ?>" class="service-item service-card">
                        <i class="fab fa-instagram"></i>
                        <p>Follow Us!</p>
                    </a>
                    <a href="track_service.php?hotel_id=<?php echo $id; ?>&service=WhatsApp&track=<?php echo urlencode('https://wa.me/'.htmlspecialchars($hotel['whatsapp'])); ?>" class="service-item service-card">
                        <i class="fab fa-whatsapp"></i>
                        <p>Chat With Us</p>
                    </a>
                </div>

                <div class="item">
                <a href="track_service.php?hotel_id=<?php echo $id; ?>&service=Phone Call&track=<?php echo urlencode('tel:'.htmlspecialchars($hotel['phone'])); ?>" class="service-item service-card">
                        <i class="fas fa-phone-alt"></i>
                        <p>Call Us</p>
                    </a>
                    <a href="track_service.php?hotel_id=<?php echo $id; ?>&service=Local Attractions&track=<?php echo urlencode('places/lucknow_places.php?hotel_id='.$id); ?>" class="service-item service-card">
                        <i class="fas fa-map-signs"></i>
                        <p>Local Attractions</p>
                    </a>
                   
                </div>

                <div class="item">
                    <?php
                    $diningMenu = $hotel['dining_menu'];
                    if (filter_var($diningMenu, FILTER_VALIDATE_URL)) {
                        $link = htmlspecialchars($diningMenu);
                    } else {
                        $link = "dining_menu/dining_menu.php?id=" . $hotel["id"];
                    }
                    ?>
                    <a href="track_service.php?hotel_id=<?php echo $id; ?>&service=Dining Menu&track=<?php echo urlencode($link); ?>" class="service-item service-card">
                        <i class="fas fa-concierge-bell"></i>
                        <p>Dining Menu</p>
                    </a>
                    <a href="track_service.php?hotel_id=<?php echo $id; ?>&service=Amenities&track=<?php echo urlencode('amenities/amenities.php?id='.$id); ?>" class="service-item service-card">
                        <i class="fas fa-spa"></i>
                        <p>Amenities</p>
                    </a>
                </div>

                <div class="item">
                    <a href="track_service.php?hotel_id=<?php echo $id; ?>&service=TV Channels&track=#" class="service-item service-card">
                        <i class="fas fa-tv"></i>
                        <p>TV Channels</p>
                    </a>
                    <a href="https://haeavycoding.github.io/compass/" class="service-item service-card">
    <i class="fas fa-compass"></i>
    <p>Digital Compass</p>
</a>

                </div>
                <?php
            }
            // Premium Plan (3)
            elseif ($planType == 3) {
                ?>
                <!-- Premium Plan Services - Includes all services -->
                <div class="item">
                    <a href="track_service.php?hotel_id=<?php echo $id; ?>&service=Google Review&track=<?php echo urlencode('google_review/index.php?id='.$hotel['id']); ?>" class="service-item service-card">
                        <i class="fab fa-google"></i>
                        <p>Review Us</p>
                    </a>
                    <?php
                    $diningMenu = $hotel['dining_menu'];
                    if (filter_var($diningMenu, FILTER_VALIDATE_URL)) {
                        $link = htmlspecialchars($diningMenu);
                    } else {
                        $link = "dining_menu/dining_menu.php?id=" . $hotel["id"];
                    }
                    ?>
                  <a href="track_service.php?hotel_id=<?php echo $id; ?>&service=WhatsApp&track=<?php echo urlencode('https://wa.me/'.htmlspecialchars($hotel['whatsapp'])); ?>" class="service-item service-card">
                        <i class="fab fa-whatsapp"></i>
                        <p>Chat With Us</p>
                    </a>
                </div>

                <div class="item">
                    <a href="track_service.php?hotel_id=<?php echo $id; ?>&service=Facebook&track=<?php echo urlencode(htmlspecialchars($hotel['facebook_link'])); ?>" class="service-item service-card">
                        <i class="fab fa-facebook-f"></i>
                        <p>Like Us</p>
                    </a>
                    <a href="track_service.php?hotel_id=<?php echo $id; ?>&service=Phone Call&track=<?php echo urlencode('tel:'.htmlspecialchars($hotel['phone'])); ?>" class="service-item service-card">
                        <i class="fas fa-phone-alt"></i>
                        <p>Call Us</p>
                    </a>
                </div>

                <div class="item">
                    <a href="track_service.php?hotel_id=<?php echo $id; ?>&service=Instagram&track=<?php echo urlencode(htmlspecialchars($hotel['instagram_link'])); ?>" class="service-item service-card">
                        <i class="fab fa-instagram"></i>
                        <p>Follow Us!</p>
                    </a>
             
                    <a href="track_service.php?hotel_id=<?php echo $id; ?>&service=Dining Menu&track=<?php echo urlencode($link); ?>" class="service-item service-card">
                        <i class="fas fa-concierge-bell"></i>
                        <p>Dining Menu</p>
                    </a>
                </div>

                <div class="item">
                    <a href="track_service.php?hotel_id=<?php echo $id; ?>&service=Local Attractions&track=<?php echo urlencode('places/lucknow_places.php?hotel_id='.$id); ?>" class="service-item service-card">
                        <i class="fas fa-map-signs"></i>
                        <p>Local Attractions</p>
                    </a>
                    <a href="track_service.php?hotel_id=<?php echo $id; ?>&service=Find Us&track=#" class="service-item service-card" aria-disabled="true">
                        <i class="fas fa-map-marker-alt"></i>
                        <p>Find Us</p>
                    </a>
                </div>

                <div class="item">
                    <a href="track_service.php?hotel_id=<?php echo $id; ?>&service=Amenities&track=<?php echo urlencode('amenities/amenities.php?id='.$id); ?>" class="service-item service-card">
                        <i class="fas fa-spa"></i>
                        <p>Amenities</p>
                    </a>
                    <a href="track_service.php?hotel_id=<?php echo $id; ?>&service=TV Channels&track=#" class="service-item service-card">
                        <i class="fas fa-tv"></i>
                        <p>TV Channels</p>
                    </a>
                </div>

                <div class="item">
                    <a href="track_service.php?hotel_id=<?php echo $id; ?>&service=Email Us&track=<?php echo urlencode('mailto:'.htmlspecialchars($hotel['email'])); ?>" class="service-item service-card">
                        <i class="fas fa-envelope"></i>
                        <p>Email Us</p>
                    </a>
                    <a href="track_service.php?hotel_id=<?php echo $id; ?>&service=Offers&track=#" class="service-item service-card">
                        <i class="fas fa-gift"></i>
                        <p>Offers</p>
                    </a>
                </div>

                <div class="item">
                    <a href="track_service.php?hotel_id=<?php echo $id; ?>&service=Check-In&track=#" class="service-item service-card" aria-disabled="true">
                        <i class="fas fa-door-open greenoutline" aria-hidden="true"></i>
                        <p>Check-In</p>
                    </a>
                    <a href="track_service.php?hotel_id=<?php echo $id; ?>&service=WiFi&track=#" class="service-item service-card" aria-disabled="true">
                        <i class="fas fa-wifi greenoutline" aria-hidden="true"></i>
                        <p>WiFi</p>
                    </a>
                </div>

                <div class="item">
                    <a href="track_service.php?hotel_id=<?php echo $id; ?>&service=Pay Us&track=#" class="service-item service-card" aria-disabled="true">
                        <i class="fas fa-credit-card greenoutline" aria-hidden="true"></i>
                        <p>Pay Us</p>
                    </a>
                    <a href="track_service.php?hotel_id=<?php echo $id; ?>&service=Travel Destinations&track=#" class="service-item service-card" aria-disabled="true">
                        <i class="fas fa-map-marked-alt greenoutline" aria-hidden="true"></i>
                        <p>Travel Dest</p>
                    </a>
                </div>
                <div class="item">
                <a href="https://haeavycoding.github.io/compass/" class="service-item service-card">
    <i class="fas fa-compass"></i>
    <p>Digital Compass</p>
</a>
                </div>
                <?php
            } ?>
        </div>
    </div>

    <footer class="footer">
        <a href="https://yashinfosystem.com/">
            <img src="logo2 (1).svg" alt="Company Logo">
        </a>

        <p class="mb-2 px-5">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($hotel['hotel_name']); ?>. All
            Rights Reserved by Yash Infosystem.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>

    <script>
        // Enhanced clock with smooth animation
        function updateTime() {
            const now = new Date();
            const hours = now.getHours().toString().padStart(2, '0');
            const minutes = now.getMinutes().toString().padStart(2, '0');
            const seconds = now.getSeconds().toString().padStart(2, '0');

            document.getElementById("time-display").style.opacity = 0;
            setTimeout(() => {
                document.getElementById("time-display").textContent = `${hours}:${minutes}:${seconds}`;
                document.getElementById("time-display").style.opacity = 1;
            }, 150);
        }

        setInterval(updateTime, 1000);
        updateTime();

        // Initialize Owl Carousel with full width settings
        $(document).ready(function () {
            $(".owl-carousel").owlCarousel({
                loop: false,
                nav: true,
                dots: true,
                center: false,
                navText: ["←", "→"],
                responsive: {
                    0: {
                        items: 3,
                        stagePadding: 5
                    },
                    576: {
                        items: 3,
                        stagePadding: 10
                    },
                    768: {
                        items: 3,
                        stagePadding: 10
                    },
                    992: {
                        items: 3,
                        stagePadding: 10
                    },
                    1200: {
                        items: 3,
                        stagePadding: 10
                    }
                }
            });
        });
    </script>
</body>

</html>