<?php
// Start session and include config
session_start();
include_once('../../config.php');

// Function to handle redirect
function handleRedirect()
{
    header("Location: /");
    exit();
}

// Validate and sanitize hotel ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    handleRedirect();
}

$map_sql = "select * from hotels where id = $id";
$map  = mysqli_query($conn, $map_sql) or die($conn);
$map_result = mysqli_fetch_assoc($map);
// Prepare and execute query with prepared statement
$sql = "SELECT * FROM hotels WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    $hotel = mysqli_fetch_assoc($result);
} else {
    handleRedirect();
}
mysqli_stmt_close($stmt);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF protection
    if (!isset($_POST['csrf_token'])) {
        die("CSRF token missing");
    }
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("CSRF token mismatch");
    }

    // Validate ratings
    $ratings = [
        'overall_rating' => filter_input(INPUT_POST, 'overall_rating', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]),
        'rooms_rating' => filter_input(INPUT_POST, 'rooms_rating', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]),
        'service_rating' => filter_input(INPUT_POST, 'service_rating', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]),
        'location_rating' => filter_input(INPUT_POST, 'location_rating', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]])
    ];

    // Check if required ratings are present
    // if (in_array(false, $ratings, true)) {
    //     die("Invalid rating values");
    // }

    // Sanitize text inputs
    $experience = filter_input(INPUT_POST, 'experience', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
    $trip_type = filter_input(INPUT_POST, 'trip_type', FILTER_SANITIZE_STRING);
    $travel_with = filter_input(INPUT_POST, 'travel_with', FILTER_SANITIZE_STRING);

    // Process hotel descriptions
    $hotel_descriptions = [];
    if (isset($_POST['hotel_description']) && is_array($_POST['hotel_description'])) {
        foreach ($_POST['hotel_description'] as $desc) {
            $clean_desc = filter_var($desc, FILTER_SANITIZE_STRING);
            if (!empty($clean_desc)) {
                $hotel_descriptions[] = $clean_desc;
            }
        }
    }
    $hotel_description_str = !empty($hotel_descriptions) ? implode(', ', $hotel_descriptions) : null;

    // Process additional topics
    $additional_topics = [];
    if (!empty($_POST['additional_topics'])) {
        foreach ($_POST['additional_topics'] as $topic => $details) {
            $clean_topic = filter_var($topic, FILTER_SANITIZE_STRING);
            $clean_details = filter_var($details, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
            if (!empty($clean_details)) {
                $additional_topics[$clean_topic] = $clean_details;
            }
        }
    }
    $additional_topics_json = !empty($additional_topics) ? json_encode($additional_topics) : null;

    // Handle file uploads
    $media_paths = [];
    if (!empty($_FILES['media']['name'][0])) {
        $upload_dir = '../../uploads/reviews/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Allowed file types
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/quicktime'];
        $max_size = 10 * 1024 * 1024; // 10MB

        foreach ($_FILES['media']['tmp_name'] as $key => $tmp_name) {
            // Validate file
            $file_type = $_FILES['media']['type'][$key];
            $file_size = $_FILES['media']['size'][$key];
            $file_error = $_FILES['media']['error'][$key];

            if ($file_error !== UPLOAD_ERR_OK) {
                continue; // Skip problematic files
            }

            if (!in_array($file_type, $allowed_types)) {
                continue; // Skip disallowed file types
            }

            if ($file_size > $max_size) {
                continue; // Skip files that are too large
            }

            // Generate secure filename
            $original_name = basename($_FILES['media']['name'][$key]);
            $file_ext = pathinfo($original_name, PATHINFO_EXTENSION);
            $safe_name = uniqid('review_', true) . '.' . $file_ext;
            $file_path = $upload_dir . $safe_name;

            if (move_uploaded_file($tmp_name, $file_path)) {
                $media_paths[] = $file_path;
            }
        }
    }
    $media_paths_str = !empty($media_paths) ? implode(', ', $media_paths) : null;

    // Insert into database with prepared statement
    $insert_sql = "INSERT INTO reviews (
        hotel_id, overall_rating, rooms_rating, service_rating, location_rating,
        experience, trip_type, travel_with, hotel_description, topics, media, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = mysqli_prepare($conn, $insert_sql);
    mysqli_stmt_bind_param(
        $stmt,
        "iiiiissssss",
        $id,
        $ratings['overall_rating'],
        $ratings['rooms_rating'],
        $ratings['service_rating'],
        $ratings['location_rating'],
        $experience,
        $trip_type,
        $travel_with,
        $hotel_description_str,
        $additional_topics_json,
        $media_paths_str
    );

    $success = mysqli_stmt_execute($stmt);
    $review_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    if ($success) {
        $_SESSION['review_submitted'] = true;
        $_SESSION['review_id'] = $review_id;
        header("Location: " . $_SERVER['PHP_SELF'] . "?id=$id&success=1");
        exit();
    } else {
        $error_message = "Error submitting review: " . mysqli_error($conn);
    }
}

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($hotel['hotel_name'] . " - Hotel Review"); ?></title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1a73e8;
            --primary-hover: #0d5bba;
            --secondary-color: #f8f9fa;
            --border-color: #dadce0;
            --text-color: #202124;
            --text-light: #5f6368;
            --error-color: #d93025;
            --success-color: #34a853;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Roboto', Arial, sans-serif;
            font-weight: 100;
        }

        /* body {
            background-color:rgb(202, 205, 207);
            color: var(--text-color);
            line-height: 1.6;
            background-image: url('uploads/hotelranbirsmap.PNG');
        } */
        body {
            background-color: rgb(202, 205, 207);
            color: var(--text-color);
            line-height: 1.6;
            background-image: url("../../<?php echo $map_result['google_map_background'] ?>");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            position: relative;
            padding-block: 20px;
        }

        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.9);
            /* काले रंग का 50% opacity ओवरले */
            z-index: -1;
        }

        .container {
            max-width: 530px;
            padding-top: 70px !important;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            height: 95vh;
            box-shadow: 0 2px 2px rgba(0, 0, 0, 0.1);
            padding-inline: 30px;
            overflow-y: scroll;
            position: relative;

        }

        header {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }

        header p {
            /* font-size: ;
            font-weight: 500; */
            color: var(--text-color);
            text-align: center;
            letter-spacing: 1px;
            word-spacing: 2px;
        }

        .stars {
            margin: 20px 0;
        }

        .stars h3 {
            font-size: 16px;
            margin-bottom: 10px;
            color: var(--text-color);
        }

        .star-icons {
            display: flex;
            gap: 5px;
        }

        .star-center {
            width: 100%;
            justify-content: center;
        }

        .star-around {
            width: 100%;
            display: flex;
            justify-content: space-between !important;

        }

        .material-icons {
            font-size: 30px;
            color: #ccc;
            cursor: pointer;
            transition: color 0.2s;
        }

        .material-icons.active {
            color: #fbbc04;
        }

        .form-group {
            margin-bottom: 25px;
        }

        textarea {
            width: 100%;
            padding: 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 16px;
            resize: vertical;
            min-height: 120px;
            margin-top: 10px;
        }

        .add-media-btn {
            position: relative;
            background-color: #f0f7ff;
            border: none;
            border-radius: 20px;
            /* padding: 30px; */
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .add-media-btn:hover {
            border-color: var(--primary-color);
            background-color: #f0f7ff;
        }

        .add-media-btn label {
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;
        }

        .camera-icon {
            font-size: 16px;
            color: var(--primary-color);
        }

        #media-input {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            opacity: 0;
            cursor: pointer;
        }

        #media-input label {
            display: flex;
        }

        #mediaPreview {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }

        .media-item {
            position: relative;
            width: 120px;
            height: 120px;
            border-radius: 8px;
            overflow: hidden;
        }

        .media-item img,
        .media-item video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .remove-media {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(0, 0, 0, 0.6);
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        label {
            font-weight: 500;
            display: block;
            margin-bottom: 10px;
        }

        .caption {
            font-size: 14px;
            color: var(--text-light);
            margin-bottom: 10px;
        }

        .button-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }

        .button {
            padding: 10px 15px;
            border-radius: 20px;
            border: 1px solid var(--border-color);
            background: white;
            cursor: pointer;
            transition: all 0.3s;
        }

        .button.selected {
            background: #e8f0fe;
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .checkmark {
            margin-right: 5px;
            display: none;
        }

        .button.selected .checkmark {
            display: inline;
        }

        #textAreaContainer {
            margin-top: 15px;
        }

        .text-area-group {
            margin-bottom: 15px;
        }

        .text-area-group label {
            font-weight: normal;
            margin-bottom: 5px;
        }

        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .popup-content {
            background: white;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            max-width: 400px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .checkmarkk-circle {
            width: 60px;
            height: 60px;
            background: var(--success-color);
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .checkmarkk {
            width: 30px;
            height: 30px;
            border: 3px solid white;
            border-top: none;
            border-right: none;
            transform: rotate(-45deg);
            margin-top: -5px;
        }

        .popup-content h2 {
            margin-bottom: 10px;
        }

        .popup-content p {
            margin-bottom: 20px;
            color: var(--text-light);
        }

        .popup-content button {
            padding: 10px 20px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }

        .footer {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
        }

        .submit-button,
        .cancel-button {
            padding: 9px 24px;
            border-radius: 5px;
            font-weight: 400;
            cursor: pointer;
            text-decoration: none !important;
        }

        .submit-button {
            background: var(--primary-color);
            color: white;
            border: none;
        }

        .submit-button:hover {
            background: var(--primary-hover);
        }

        .submit-button:disabled {
            background: #bdc1c6;
            cursor: not-allowed;
        }

        .cancel-button {
            background: white;
            border: 1px solid var(--border-color);
            color: var(--text-light);
        }

        .cancel-button:hover {
            background: var(--secondary-color);
        }

        #experience {
            border-radius: 4px;
            border-color: rgb(50, 139, 218);
            border-width: 2px;

        }

        .guest {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-name {
            display: flex;
            flex-direction: column;
        }

        .name {
            font-weight: bold;
            font-size: 18px;
        }

        .googlepost {
            font-size: 14px;
            color: gray;
            position: relative;
        }

        .info-icon {
            color: gray;
            margin-left: 5px;
            cursor: pointer;
        }

        /* Popup Box Styling */
        .popup-box {
            display: none;
            position: absolute;
            top: 30px;
            left: 80%;
            width: 250px;
            /* height: 300px; */
            background-color: #fff;
            color: #333;
            /* border: 1px solid #ccc; */
            border-radius: 10PX;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            z-index: 100;
            /* overflow-y: auto; */
        }

        .popup-box::before {
            content: '';
            position: absolute;
            top: -25PX;
            left: 20px;
            border-width: 15px;
            border-style: solid;
            border-color: transparent transparent #ccc transparent;
        }

        .popup-box P {
            font-size: 12PX;
        }

        /* For Chrome, Edge, Safari */
        ::-webkit-scrollbar {
            width: 0;
            /* Vertical scrollbar width */
            height: 0;
            /* Horizontal scrollbar height */

        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            /* Track color */
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: #888;
            /* Scroll handle color */
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #555;
            /* Scroll handle on hover */
        }

        /* Firefox */
        * {
            /* scrollbar-width: thin; */
            /* auto, thin, none */
            /* scrollbar-color: #888 #f1f1f1; */
            /* thumb color + track color */
        }
        .container header{
            height: auto;
            position: absolute;
            top: 0;
            left: 0;
            padding: 20px;
            text-align: center;
            background-color: white;
            width: 100%;
            z-index: 110;
            border: none;
        }
        .container .footer{
            height: auto;
            position: sticky;
            bottom: 0;
            left: 0;
            padding: 20px 0;
            text-align: center;
            background-color: white;
            width: 100%;
            z-index: 110;
            border: none;
        }

        @media (max-width: 568px) {

            .button-container {
                gap: 8px;
            }

            .button {
                padding: 8px 16px;
                font-size: 14px;
            }

            body {
            padding: 0;
        }
            .media-item {
                width: 100px;
                height: 100px;
            }
            .container{
                height: 100vh;
                width: 100%;
                border-radius: 0;
                overflow-x: hidden;
            }
            .popup-box {
            display: none;
            position: absolute;
            top: 30px;
            left: 20%;
            width: 250px;
            /* height: 300px; */
            background-color: #fff;
            color: #333;
            /* border: 1px solid #ccc; */
            border-radius: 10PX;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            z-index: 100;
            /* overflow-y: auto; */
        }

        .popup-box::before {
            content: '';
            position: absolute;
            top: -25PX;
            left: 153px;
            border-width: 15px;
            border-style: solid;
            border-color: transparent transparent #ccc transparent;
        }
        }
    </style>
</head>
<!-- Font Awesome CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<body>
    <div class="container">
    <header>
    <p>
        <?php
        $hotelName = htmlspecialchars($hotel['hotel_name']);
        $locationWords = explode(" ", $hotel['location']);
        $shortLocation = htmlspecialchars($locationWords[0] ?? '') . " " . htmlspecialchars($locationWords[1] ?? '');
        echo $hotelName . " - " . $shortLocation;
        ?>
    </p>
</header>


        <?php if (isset($error_message)): ?>
            <div style="color: var(--error-color); margin-bottom: 20px;">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <div class="guest">
            <i class="fas fa-user-circle" style="font-size: 40px; color: gray;"></i>
            <div class="user-name">
                <div class="name">Dear User</div>
                <div class="googlepost">
                    Posting publicly across Google

                    <!-- Info Icon -->
                    <i class="fa fa-info-circle info-icon" aria-hidden="true" onclick="togglePopup()"></i>

                    <!-- POPUP BOX -->
                    <div id="infoPopup" class="popup-box">
                        <p>
                            Posts may appear publicly with your profile name, picture, or link to your profile.
                            Posts must follow Google’s policies.
                            Posts may appear on and be used across Google services including Maps, Search, and YouTube
                            and 3rd party sites and apps that use Google services.
                            You can delete your post anytime.
                            See content policy
                            For beta questions, your answers may not be displayed publicly during the experiment and may
                            only be visible to you and/or others participating in the experiment. We may delete answers
                            after the experiment.
                        </p>
                    </div>
                </div>
            </div>
        </div>



        <form id="reviewForm" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <input type="hidden" name="hotel_id" value="<?php echo $id; ?>">

            <!-- Hidden rating inputs -->
            <input type="hidden" id="overall_rating" name="overall_rating" value="0">
            <input type="hidden" id="rooms_rating" name="rooms_rating" value="0">
            <input type="hidden" id="service_rating" name="service_rating" value="0">
            <input type="hidden" id="location_rating" name="location_rating" value="0">

            <!-- Overall Rating -->
            <div class="stars all">
                <!-- <h3>Overall Rating</h3> -->
                <div class="star-icons star-center" id="overall-stars">
                    <i class="material-icons" data-value="1" data-category="overall">star_border</i>
                    <i class="material-icons" data-value="2" data-category="overall">star_border</i>
                    <i class="material-icons" data-value="3" data-category="overall">star_border</i>
                    <i class="material-icons" data-value="4" data-category="overall">star_border</i>
                    <i class="material-icons" data-value="5" data-category="overall">star_border</i>
                </div>
            </div>

            <!-- Category Ratings -->
            <div class="stars star-around">
                <h3>Rooms</h3>
                <div class="star-icons" id="rooms-stars">
                    <i class="material-icons" data-value="1" data-category="rooms">star_border</i>
                    <i class="material-icons" data-value="2" data-category="rooms">star_border</i>
                    <i class="material-icons" data-value="3" data-category="rooms">star_border</i>
                    <i class="material-icons" data-value="4" data-category="rooms">star_border</i>
                    <i class="material-icons" data-value="5" data-category="rooms">star_border</i>
                </div>
            </div>

            <div class="stars star-around">
                <h3>Service</h3>
                <div class="star-icons" id="service-stars">
                    <i class="material-icons" data-value="1" data-category="service">star_border</i>
                    <i class="material-icons" data-value="2" data-category="service">star_border</i>
                    <i class="material-icons" data-value="3" data-category="service">star_border</i>
                    <i class="material-icons" data-value="4" data-category="service">star_border</i>
                    <i class="material-icons" data-value="5" data-category="service">star_border</i>
                </div>
            </div>

            <div class="stars star-around">
                <h3>Location</h3>
                <div class="star-icons" id="location-stars">
                    <i class="material-icons" data-value="1" data-category="location">star_border</i>
                    <i class="material-icons" data-value="2" data-category="location">star_border</i>
                    <i class="material-icons" data-value="3" data-category="location">star_border</i>
                    <i class="material-icons" data-value="4" data-category="location">star_border</i>
                    <i class="material-icons" data-value="5" data-category="location">star_border</i>
                </div>
            </div>

            <!-- Experience -->
            <div class="form-group">
                <textarea id="experience" name="experience"
                    placeholder="Share details of your own experience at this place..."></textarea>
            </div>

            <!-- Media Upload -->
            <div class="form-group">
                <div class="add-media-btn">
                    <label for="media-input">
                        <span class="camera-icon">&#128247;</span>
                        <span>Add photos and videos</span>
                    </label>
                    <input type="file" id="media-input" name="media[]" accept="image/*,video/*" multiple>
                </div>
                <div id="mediaPreview"></div>
            </div>

            <!-- Trip Type -->
            <div class="form-group">
                <label>What kind of trip was it?</label>
                <div class="button-container" id="trip-type">
                    <button type="button" class="button" data-value="Business"
                        onclick="toggleButton(this, 'trip-type')">
                        <span class="checkmark">✓</span> Business
                    </button>
                    <button type="button" class="button" data-value="Vacation"
                        onclick="toggleButton(this, 'trip-type')">
                        <span class="checkmark">✓</span> Vacation
                    </button>
                    <input type="hidden" name="trip_type" value="">
                </div>
            </div>

            <!-- Travel With -->
            <div class="form-group">
                <label>Who did you travel with?</label>
                <div class="button-container" id="travel-with">
                    <button type="button" class="button" data-value="Family"
                        onclick="toggleButton(this, 'travel-with')">
                        <span class="checkmark">✓</span> Family
                    </button>
                    <button type="button" class="button" data-value="Friends"
                        onclick="toggleButton(this, 'travel-with')">
                        <span class="checkmark">✓</span> Friends
                    </button>
                    <button type="button" class="button" data-value="Couple"
                        onclick="toggleButton(this, 'travel-with')">
                        <span class="checkmark">✓</span> Couple
                    </button>
                    <button type="button" class="button" data-value="Solo" onclick="toggleButton(this, 'travel-with')">
                        <span class="checkmark">✓</span> Solo
                    </button>
                    <input type="hidden" name="travel_with" value="">
                </div>
            </div>

            <!-- Hotel Description -->
            <div class="form-group">
                <label>How would you describe the hotel?</label>
                <div class="caption">(Select all that apply)</div>
                <div class="button-container" id="hotel-description">
                    <button type="button" class="button" data-value="Luxury" onclick="toggleMultiSelect(this)">
                        <span class="checkmark">✓</span> Luxury
                    </button>
                    <button type="button" class="button" data-value="Great View" onclick="toggleMultiSelect(this)">
                        <span class="checkmark">✓</span> Great View
                    </button>
                    <button type="button" class="button" data-value="Romantic" onclick="toggleMultiSelect(this)">
                        <span class="checkmark">✓</span> Romantic
                    </button>
                    <button type="button" class="button" data-value="Quiet" onclick="toggleMultiSelect(this)">
                        <span class="checkmark">✓</span> Quiet
                    </button>
                    <button type="button" class="button" data-value="Kid-friendly" onclick="toggleMultiSelect(this)">
                        <span class="checkmark">✓</span> Kid-friendly
                    </button>
                    <button type="button" class="button" data-value="Great Value" onclick="toggleMultiSelect(this)">
                        <span class="checkmark">✓</span> Great Value
                    </button>
                    <button type="button" class="button" data-value="High-tech" onclick="toggleMultiSelect(this)">
                        <span class="checkmark">✓</span> High-tech
                    </button>
                </div>
                <input type="hidden" name="hotel_description[]" value="">
            </div>

            <!-- Additional Topics -->
            <div class="form-group">
                <label>Can you share more about these topics?</label>
                <div class="caption">(Select all that apply)</div>
                <div class="button-container" id="additional-topics">
                    <button type="button" class="button" data-topic="Rooms" onclick="toggleTextbox(this)">
                        Rooms
                    </button>
                    <button type="button" class="button" data-topic="Nearby Activity" onclick="toggleTextbox(this)">
                        Nearby Activity
                    </button>
                    <button type="button" class="button" data-topic="Safety" onclick="toggleTextbox(this)">
                        Safety
                    </button>
                    <button type="button" class="button" data-topic="Walkability" onclick="toggleTextbox(this)">
                        Walkability
                    </button>
                    <button type="button" class="button" data-topic="Food & drinks" onclick="toggleTextbox(this)">
                        Food & drinks
                    </button>
                    <button type="button" class="button" data-topic="Noteworthy details" onclick="toggleTextbox(this)">
                        Noteworthy details
                    </button>
                </div>
                <div id="textAreaContainer"></div>
            </div>

                <div class="footer">
                    <a href="<?php echo $map_result['google_review_link']; ?>"<button type="button" class="cancel-button" onclick="cancelReview()">Cancel</button></a>
                    <button type="submit" id="submitReview" class="submit-button" disabled>Post</button>

                </div>
        </form>
    </div>

    <!-- Thank You Popup -->
    <div id="thankYouPopup" class="popup-overlay"
        style="<?php echo (isset($_GET['success'])) ? 'display: flex;' : 'display: none;' ?>">
        <div class="popup-content">
            <div class="popup-header">
                <div class="checkmarkk-circle">
                    <div class="checkmarkk"></div>
                </div>
            </div>
            <h2>Thank You!</h2>
            <p>Your review has been successfully<br> submitted.</p>
            <button id="closePopupButton">OK</button>
        </div>
    </div>

    <script>
        // Star Rating Functionality
        document.querySelectorAll('.star-icons').forEach(starsContainer => {
            const stars = starsContainer.querySelectorAll('.material-icons');
            const category = starsContainer.id.replace('-stars', '');
            const hiddenInput = document.getElementById(`${category}_rating`);

            stars.forEach(star => {
                star.addEventListener('click', () => {
                    const value = parseInt(star.getAttribute('data-value'));
                    hiddenInput.value = value;

                    // Update stars display
                    stars.forEach((s, index) => {
                        if (index < value) {
                            s.textContent = 'star';
                            s.classList.add('active');
                        } else {
                            s.textContent = 'star_border';
                            s.classList.remove('active');
                        }
                    });

                    checkFormCompletion();
                });

                star.addEventListener('mouseover', () => {
                    const hoverValue = parseInt(star.getAttribute('data-value'));
                    stars.forEach((s, index) => {
                        if (index < hoverValue) {
                            s.textContent = 'star';
                        } else {
                            s.textContent = 'star_border';
                        }
                    });
                });

                star.addEventListener('mouseout', () => {
                    const currentValue = hiddenInput.value;
                    stars.forEach((s, index) => {
                        if (index < currentValue) {
                            s.textContent = 'star';
                        } else {
                            s.textContent = 'star_border';
                        }
                    });
                });
            });
        });

        // Single Select Buttons (Trip Type, Travel With)
        function toggleButton(button, containerId) {
            const container = document.getElementById(containerId);
            container.querySelectorAll('.button').forEach(btn => {
                btn.classList.remove('selected');
            });
            button.classList.add('selected');

            const hiddenInput = container.querySelector('input[type="hidden"]');
            hiddenInput.value = button.getAttribute('data-value');
            checkFormCompletion();
        }

        // Multi Select Buttons (Hotel Description)
        function toggleMultiSelect(button) {
            button.classList.toggle('selected');

            const selectedValues = [];
            document.querySelectorAll('#hotel-description .button.selected').forEach(btn => {
                selectedValues.push(btn.getAttribute('data-value'));
            });

            const hiddenInput = document.querySelector('input[name="hotel_description[]"]');
            hiddenInput.value = selectedValues.join(', ');
        }

        // Additional Topics with Textareas
        function toggleTextbox(button) {
            button.classList.toggle('selected');
            const topic = button.getAttribute('data-topic');
            const textareaId = `topic-${topic.replace(/\s+/g, '-').toLowerCase()}`;
            const textareaContainer = document.getElementById('textAreaContainer');

            if (button.classList.contains('selected')) {
                // Add textarea
                const div = document.createElement('div');
                div.className = 'text-area-group';
                div.innerHTML = `
                    <label>${topic}</label>
                    <textarea name="additional_topics[${topic}]" id="${textareaId}"
                              placeholder="Share details about ${topic}"></textarea>
                `;
                textareaContainer.appendChild(div);
            } else {
                // Remove textarea
                const textareaDiv = document.getElementById(textareaId)?.parentElement;
                if (textareaDiv) {
                    textareaContainer.removeChild(textareaDiv);
                }
            }
        }

        // Media Upload Preview
        document.getElementById('media-input').addEventListener('change', function (e) {
            const preview = document.getElementById('mediaPreview');
            preview.innerHTML = '';

            if (this.files && this.files.length > 0) {
                Array.from(this.files).forEach(file => {
                    // Limit to 10 files
                    if (preview.children.length >= 10) return;

                    const reader = new FileReader();

                    reader.onload = function (event) {
                        const mediaItem = document.createElement('div');
                        mediaItem.className = 'media-item';

                        const mediaElement = file.type.startsWith('image') ?
                            document.createElement('img') :
                            document.createElement('video');

                        mediaElement.src = event.target.result;
                        if (file.type.startsWith('video')) {
                            mediaElement.controls = true;
                        }

                        const removeBtn = document.createElement('button');
                        removeBtn.className = 'remove-media';
                        removeBtn.innerHTML = '&times;';
                        removeBtn.addEventListener('click', (e) => {
                            e.preventDefault();
                            mediaItem.remove();
                        });

                        mediaItem.appendChild(mediaElement);
                        mediaItem.appendChild(removeBtn);
                        preview.appendChild(mediaItem);
                    };

                    reader.readAsDataURL(file);
                });
            }
        });

        function checkFormCompletion() {
            const overallRating = document.getElementById('overall_rating').value;
            const submitButton = document.getElementById('submitReview');

            // Enable/Disable Submit Button based on overall rating
            submitButton.disabled = !(overallRating > 0);

            // Redirect if overall rating is 4 or 5
            if (overallRating >= 4) {
                window.location.href = "<?php echo htmlspecialchars($hotel['google_review_link']); ?>";
            }
        }
        // Cancel Button
        function cancelReview() {
            if (confirm('Are you sure you want to cancel? Your review will not be saved.')) {
                window.location.href = '/';
            }
        }

        // Close Popup
        document.getElementById('closePopupButton').addEventListener('click', function () {
            history.back();
            history.back();
            history.back();
        });
    </script>
    <script>
        // Show popup when window loads
        window.onload = function () {
            const popup = document.getElementById('infoPopup');
            popup.style.display = 'block';
        }

        // Toggle popup on icon click
        function togglePopup() {
            const popup = document.getElementById('infoPopup');
            popup.style.display = popup.style.display === 'block' ? 'none' : 'block';
        }

        // Close popup when clicking outside
        window.addEventListener('click', function (e) {
            const popup = document.getElementById('infoPopup');
            if (!e.target.closest('.googlepost')) {
                popup.style.display = 'none';
            }
        });
    </script>
</body>

</html>
