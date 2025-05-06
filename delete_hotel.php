<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

include 'config.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Basic sanitation

    // Delete associated clients
    mysqli_query($conn, "DELETE FROM clients WHERE hotel_id = $id");

   // Delete associated reviews and their media files
$reviews_result = mysqli_query($conn, "SELECT media FROM reviews WHERE hotel_id = $id");

if (mysqli_num_rows($reviews_result) > 0) {
    while ($review = mysqli_fetch_assoc($reviews_result)) {
        if (!empty($review['media'])) {
            // Construct full path to the media file
            $media_path = 'uploads/reviews/' . basename($review['media']);

            // Check and delete the file
            if (file_exists($media_path)) {
                unlink($media_path);
            }
        }
    }

    // Delete all reviews for this hotel
    mysqli_query($conn, "DELETE FROM reviews WHERE hotel_id = $id");
}
$item_visibility_result = mysqli_query($conn, "SELECT * FROM item_visibility WHERE hotel_id = $id");
if (mysqli_num_rows($item_visibility_result) > 0) {

    // Delete all reviews for this hotel
    mysqli_query($conn, "DELETE FROM item_visibility WHERE hotel_id = $id");
}

    // uploads\reviews\review_68148b434de904.35771085.png
    // Fetch hotel images
    $result = mysqli_query($conn, "SELECT image_url, logo_of_hotel, google_map_background FROM hotels WHERE id = $id");
    if (mysqli_num_rows($result) === 1) {
        $hotel = mysqli_fetch_assoc($result);

        $files = [
            $hotel['image_url'],
            $hotel['logo_of_hotel'],
            $hotel['google_map_background']
        ];

        foreach ($files as $file) {
            if (!empty($file) && file_exists($file)) {
                unlink($file);
            }
        }

        // Delete the hotel record
        mysqli_query($conn, "DELETE FROM hotels WHERE id = $id");

        header("Location: " . $_SERVER['HTTP_REFERER'] . "?deleted=1");
        exit();
    } else {
        header("Location: " . $_SERVER['HTTP_REFERER'] . "?error=not_found");
        exit();
    }
} else {
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

?>
