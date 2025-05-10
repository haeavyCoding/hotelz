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

$hotel = $result->fetch_assoc();
$planType = $hotel['plan_type'];

// Fetch visibility status for all items
$visibilitySql = "SELECT item_name, is_visible FROM item_visibility WHERE hotel_id = $hotelId";
$visibilityResult = $conn->query($visibilitySql);
$itemVisibility = [];

if ($visibilityResult->num_rows > 0) {
    while ($row = $visibilityResult->fetch_assoc()) {
        $itemVisibility[$row['item_name']] = $row['is_visible'];
    }
}

// Function to check if item should be displayed
function shouldDisplayItem($itemName, $itemVisibility) {
    return !isset($itemVisibility[$itemName]) || (bool) $itemVisibility[$itemName];
}

// Increment visit counter and track visit
$conn->begin_transaction();
try {
    $updateStmt = $conn->prepare("UPDATE hotels SET visit_count = visit_count + 1 WHERE id = ?");
    $updateStmt->bind_param("i", $hotelId);
    $updateStmt->execute();

    $message = "New visit to " . $hotel['hotel_name'];
    $notifStmt = $conn->prepare("INSERT INTO notifications (hotel_id, message) VALUES (?, ?)");
    $notifStmt->bind_param("is", $hotelId, $message);
    $notifStmt->execute();

    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
}

// Prepare visible items based on plan type and visibility settings
$visibleItems = [];

// Common items for all plans
$commonItems = [
    'google_review' => [
        'icon' => 'fab fa-google',
        'text' => 'Review Us',
        'link' => ($planType == 1) ? htmlspecialchars($hotel['google_review_link']) : "google_review/index.php?id=" . $hotel['id']
    ],
    'phone' => [
        'icon' => 'fas fa-phone-alt',
        'text' => 'Call Us',
        'link' => 'tel:' . htmlspecialchars($hotel['phone'])
    ],
    'facebook' => [
        'icon' => 'fab fa-facebook-f',
        'text' => 'Like Us',
        'link' => htmlspecialchars($hotel['facebook_link'])
    ],
    'whatsapp' => [
        'icon' => 'fab fa-whatsapp',
        'text' => 'Chat With Us',
        'link' => 'https://wa.me/' . htmlspecialchars($hotel['whatsapp'])
    ],
    'instagram' => [
        'icon' => 'fab fa-instagram',
        'text' => 'Follow Us!',
        'link' => htmlspecialchars($hotel['instagram_link'])
    ]
];

// Basic plan specific items
$basicItems = [
    'find_us' => [
        'icon' => 'fas fa-map-marker-alt',
        'text' => 'Find Us',
        'link' => 'find_us.php?hotel_id=' . $hotelId
    ]
];

// Advanced and Premium plan items
$advancedItems = [
    'dining_menu' => [
        'icon' => 'fas fa-concierge-bell',
        'text' => 'Dining Menu',
        'link' => filter_var($hotel['dining_menu'], FILTER_VALIDATE_URL) ? 
                 htmlspecialchars($hotel['dining_menu']) : 
                 "dining_menu/dining_menu.php?id=" . $hotel['id']
    ],
    'local_attractions' => [
        'icon' => 'fas fa-map-signs',
        'text' => 'Local Attractions',
        'link' => 'places/lucknow_places.php?hotel_id=' . $hotelId
    ],
    'amenities' => [
        'icon' => 'fas fa-spa',
        'text' => 'Amenities',
        'link' => 'amenities.php?hotel_id=' . $hotelId
    ]
];

// Premium plan only items
$premiumItems = [
    'tv_channels' => [
        'icon' => 'fas fa-tv',
        'text' => 'TV Channels',
        'link' => '#'
    ],
    'offers' => [
        'icon' => 'fas fa-gift',
        'text' => 'Offers',
        'link' => '#'
    ],
    'check_in' => [
        'icon' => 'fas fa-door-open',
        'text' => 'Check-In',
        'link' => '#'
    ],
    'wifi' => [
        'icon' => 'fas fa-wifi',
        'text' => 'WiFi',
        'link' => '#'
    ],
    'pay_us' => [
        'icon' => 'fas fa-credit-card',
        'text' => 'Pay Us',
        'link' => '#'
    ],
    'travel_destinations' => [
        'icon' => 'fas fa-map-marked-alt',
        'text' => 'Travel Dest',
        'link' => '#'
    ]
];

// Add items based on plan type
foreach ($commonItems as $itemName => $item) {
    if (shouldDisplayItem($itemName, $itemVisibility)) {
        $visibleItems[] = [
            'icon' => $item['icon'],
            'text' => $item['text'],
            'link' => "track_service.php?hotel_id=$hotelId&service=" . 
                     urlencode($item['text']) . "&track=" . urlencode($item['link'])
        ];
    }
}

if ($planType == 1) { // Basic plan
    foreach ($basicItems as $itemName => $item) {
        if (shouldDisplayItem($itemName, $itemVisibility)) {
            $visibleItems[] = [
                'icon' => $item['icon'],
                'text' => $item['text'],
                'link' => "track_service.php?hotel_id=$hotelId&service=" . 
                         urlencode($item['text']) . "&track=" . urlencode($item['link'])
            ];
        }
    }
} elseif ($planType >= 2) { // Advanced and Premium plans
    foreach ($advancedItems as $itemName => $item) {
        if (shouldDisplayItem($itemName, $itemVisibility)) {
            $visibleItems[] = [
                'icon' => $item['icon'],
                'text' => $item['text'],
                'link' => "track_service.php?hotel_id=$hotelId&service=" . 
                         urlencode($item['text']) . "&track=" . urlencode($item['link'])
            ];
        }
    }
    
    if ($planType == 3) { // Premium plan only
        foreach ($premiumItems as $itemName => $item) {
            if (shouldDisplayItem($itemName, $itemVisibility)) {
                $visibleItems[] = [
                    'icon' => $item['icon'],
                    'text' => $item['text'],
                    'link' => "track_service.php?hotel_id=$hotelId&service=" . 
                             urlencode($item['text']) . "&track=" . urlencode($item['link'])
                ];
            }
        }
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
   
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
    <!-- Your existing HTML structure remains the same -->
    <div class="background-image">
        <?php
        $imagePath = $hotel['image_url'];
        if (!empty($hotel['image_url']) && file_exists($imagePath)) {
            echo "<img src='$imagePath' alt='Hotel Image'>";
        } else {
            echo "<img src='hotel-img.jpg' alt='Default Hotel Image'>";
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
        <p class="mb-2 px-5">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($hotel['hotel_name']); ?>. All Rights Reserved by Yash Infosystem.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
    <script>
        // Clock and carousel initialization scripts remain the same
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

        $(document).ready(function () {
            var itemCount = $(".owl-carousel .item").length;
            $(".owl-carousel").owlCarousel({
                nav: itemCount > 1,
                dots: itemCount > 1,
                center: false,
                navText: ["←", "→"],
                responsive: {
                    0: { items: Math.min(3, itemCount), stagePadding: 5 },
                    576: { items: Math.min(3, itemCount), stagePadding: 10 },
                    768: { items: Math.min(3, itemCount), stagePadding: 10 },
                    992: { items: Math.min(3, itemCount), stagePadding: 10 },
                    1200: { items: Math.min(3, itemCount), stagePadding: 10 }
                }
            });

            if (itemCount <= 1) {
                $('.owl-nav').hide();
            }
        });
    </script>
</body>
</html>