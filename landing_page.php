<?php
require_once 'config.php';
// Check if ID exists and is valid
if (!isset($_GET['id'])) {
    header("Location: sorry.php?reason=invalid_request");
    exit();
}

$hotelId = (int) $_GET['id'];
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
    $hotelId = (int) $_GET['id'];
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

// Fetch visibility status for all items
$visibilitySql = "SELECT item_name, is_visible FROM item_visibility WHERE hotel_id = $id";
$visibilityResult = $conn->query($visibilitySql);
$itemVisibility = [];

if ($visibilityResult->num_rows > 0) {
    while ($row = $visibilityResult->fetch_assoc()) {
        $itemVisibility[$row['item_name']] = $row['is_visible'];
    }
}

// Function to check if item should be displayed
function shouldDisplayItem($itemName, $itemVisibility)
{
    // If no record exists or is_visible is true, display the item
    return !isset($itemVisibility[$itemName]) || (bool) $itemVisibility[$itemName];
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
    function trackServiceClick($conn, $hotelId, $serviceName)
    {
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

// Prepare visible items based on plan type and visibility settings
$visibleItems = [];
$planType = $hotel['plan_type'];

// Common items for all plans
if (shouldDisplayItem('google_review', $itemVisibility)) {
    $link = ($planType == 1) ? htmlspecialchars($hotel['google_review_link']) : "google_review/index.php?id=" . $hotel['id'];
    $visibleItems[] = [
        'icon' => 'fab fa-google',
        'text' => 'Review Us',
        'link' => "track_service.php?hotel_id=$id&service=Google Review&track=" . urlencode($link)
    ];
}

if (shouldDisplayItem('instagram', $itemVisibility)) {
    $visibleItems[] = [
        'icon' => 'fab fa-instagram',
        'text' => 'Follow Us!',
        'link' => "track_service.php?hotel_id=$id&service=Instagram&track=" . urlencode(htmlspecialchars($hotel['instagram_link']))
    ];
}

if (shouldDisplayItem('facebook', $itemVisibility)) {
    $visibleItems[] = [
        'icon' => 'fab fa-facebook-f',
        'text' => 'Like Us',
        'link' => "track_service.php?hotel_id=$id&service=Facebook&track=" . urlencode(htmlspecialchars($hotel['facebook_link']))
    ];
}

if (shouldDisplayItem('whatsapp', $itemVisibility)) {
    $visibleItems[] = [
        'icon' => 'fab fa-whatsapp',
        'text' => 'Chat With Us',
        'link' => "track_service.php?hotel_id=$id&service=WhatsApp&track=" . urlencode('https://wa.me/' . htmlspecialchars($hotel['whatsapp']))
    ];
}

if (shouldDisplayItem('phone', $itemVisibility)) {
    $visibleItems[] = [
        'icon' => 'fas fa-phone-alt',
        'text' => 'Call Us',
        'link' => "track_service.php?hotel_id=$id&service=Phone Call&track=" . urlencode('tel:' . htmlspecialchars($hotel['phone']))
    ];
}

if (shouldDisplayItem('local_attractions', $itemVisibility)) {
    $visibleItems[] = [
        'icon' => 'fas fa-map-signs',
        'text' => 'Local Attractions',
        'link' => "track_service.php?hotel_id=$id&service=Local Attractions&track=" . urlencode('places/lucknow_places.php?hotel_id=' . $id)
    ];
}

// Advanced and Premium plan items
if ($planType >= 2) {
    if (shouldDisplayItem('dining_menu', $itemVisibility)) {
        $diningMenu = $hotel['dining_menu'];
        $link = filter_var($diningMenu, FILTER_VALIDATE_URL) ? htmlspecialchars($diningMenu) : "dining_menu/dining_menu.php?id=" . $hotel["id"];
        $visibleItems[] = [
            'icon' => 'fas fa-concierge-bell',
            'text' => 'Dining Menu',
            'link' => "track_service.php?hotel_id=$id&service=Dining Menu&track=" . urlencode($link)
        ];
    }

    if (shouldDisplayItem('amenities', $itemVisibility)) {
        $visibleItems[] = [
            'icon' => 'fas fa-spa',
            'text' => 'Amenities',
            'link' => "track_service.php?hotel_id=$id&service=Amenities&track=" . urlencode('amenities.php?hotel_id=' . $id)
        ];
    }

    if (shouldDisplayItem('tv_channels', $itemVisibility)) {
        $visibleItems[] = [
            'icon' => 'fas fa-tv',
            'text' => 'TV Channels',
            'link' => "track_service.php?hotel_id=$id&service=TV Channels&track=#"
        ];
    }
}

// Premium plan only items
if ($planType == 3) {
    if (shouldDisplayItem('email', $itemVisibility)) {
        $visibleItems[] = [
            'icon' => 'fas fa-envelope',
            'text' => 'Email Us',
            'link' => "track_service.php?hotel_id=$id&service=Email Us&track=" . urlencode('mailto:' . htmlspecialchars($hotel['email']))
        ];
    }

    if (shouldDisplayItem('offers', $itemVisibility)) {
        $visibleItems[] = [
            'icon' => 'fas fa-gift',
            'text' => 'Offers',
            'link' => "track_service.php?hotel_id=$id&service=Offers&track=#"
        ];
    }

    if (shouldDisplayItem('check_in', $itemVisibility)) {
        $visibleItems[] = [
            'icon' => 'fas fa-door-open',
            'text' => 'Check-In',
            'link' => "track_service.php?hotel_id=$id&service=Check-In&track=#"
        ];
    }

    if (shouldDisplayItem('wifi', $itemVisibility)) {
        $visibleItems[] = [
            'icon' => 'fas fa-wifi',
            'text' => 'WiFi',
            'link' => "track_service.php?hotel_id=$id&service=WiFi&track=#"
        ];
    }

    if (shouldDisplayItem('pay_us', $itemVisibility)) {
        $visibleItems[] = [
            'icon' => 'fas fa-credit-card',
            'text' => 'Pay Us',
            'link' => "track_service.php?hotel_id=$id&service=Pay Us&track=#"
        ];
    }

    if (shouldDisplayItem('travel_destinations', $itemVisibility)) {
        $visibleItems[] = [
            'icon' => 'fas fa-map-marked-alt',
            'text' => 'Travel Dest',
            'link' => "track_service.php?hotel_id=$id&service=Travel Destinations&track=#"
        ];
    }
}

// Group items into pairs for the carousel
$groupedItems = array_chunk($visibleItems, 2);
$carouselClass = (count($groupedItems) > 1) ? 'multi-item' : 'single-item-carousel';
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

        .content-1 h3 {
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

        /* Single item carousel styles */
        .single-item-carousel .owl-stage {
            display: flex;
            justify-content: center;
        }

        /* Hide dots when only one item */
        .owl-carousel:not(.multi-item) .owl-dots {
            display: none !important;
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
            * {
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

            .content-1 h3 {
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

        .compass-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            /* margin: 15px; */
        }

        .compass-icon {
            background: linear-gradient(145deg, #003049, #2a6f97);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            color: #ffd700;
            align-items: center;
            justify-content: center;
            font-size: 30px !important;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.25);
            transition: all 0.3s ease;
        }

        .compass-icon:hover {
            background: linear-gradient(145deg, #0077b6, #023e8a);
            color: #ffd700;
            scale: 1.1;
            /* Gold hover effect */
        }

        @media (max-width: 600px) {
            .compass-icon {
                width: 35px;
                height: 35px;
            }

            .compass-icon>i {
                font-size: 30px !important;
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
                <div class="compass-container" title="Digital Compass">
                    <a href="compass.php" class="compass-icon" title="Digital Compass">
                        🧭
                    </a>
                </div>

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
        <div class="owl-carousel owl-theme <?php echo $carouselClass; ?>">
            <?php foreach ($groupedItems as $itemGroup): ?>
                <div class="item">
                    <?php foreach ($itemGroup as $item): ?>
                        <a href="<?php echo $item['link']; ?>" class="service-item service-card">
                            <i class="<?php echo $item['icon']; ?>"></i>
                            <p><?php echo $item['text']; ?></p>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
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

        // Initialize Owl Carousel with dynamic settings
        $(document).ready(function () {
            var itemCount = $(".owl-carousel .item").length;

            $(".owl-carousel").owlCarousel({
                // loop: itemCount > 1, // Only loop if we have more than one item
                nav: itemCount > 1,  // Only show nav if we have more than one item
                dots: itemCount > 1, // Only show dots if we have more than one item
                center: false,
                navText: ["←", "→"],
                responsive: {
                    0: {
                        items: Math.min(3, itemCount),
                        stagePadding: 5
                    },
                    576: {
                        items: Math.min(3, itemCount),
                        stagePadding: 10
                    },
                    768: {
                        items: Math.min(3, itemCount),
                        stagePadding: 10
                    },
                    992: {
                        items: Math.min(3, itemCount),
                        stagePadding: 10
                    },
                    1200: {
                        items: Math.min(3, itemCount),
                        stagePadding: 10
                    }
                }
            });

            // Hide navigation if only one item
            if (itemCount <= 1) {
                $('.owl-nav').hide();
            }
        });
    </script>
</body>

</html>