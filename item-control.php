<?php
require_once 'config.php';

// Check if user is admin (you should implement proper authentication)
// session_start();
// if (!isset($_SESSION['admin_logged_in'])) {
//     header("Location: admin-login.php");
//     exit();
// }

// Get hotel ID from URL
if (!isset($_GET['hotel_id'])) {
    die("Hotel ID not specified");
}
$hotelId = (int)$_GET['hotel_id'];

// Fetch current item statuses
$sql = "SELECT * FROM item_visibility WHERE hotel_id = $hotelId";
$result = $conn->query($sql);
$currentStatuses = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $currentStatuses[$row['item_name']] = $row['is_visible'];
    }
}

// Default items with their display names
$allItems = [
    'google_review' => 'Google Review',
    'facebook' => 'Facebook',
    'instagram' => 'Instagram',
    'whatsapp' => 'WhatsApp',
    'phone' => 'Phone Call',
    'local_attractions' => 'Local Attractions',
    'dining_menu' => 'Dining Menu',
    'amenities' => 'Amenities',
    'tv_channels' => 'TV Channels',
    'email' => 'Email Us',
    'offers' => 'Offers',
    'check_in' => 'Check-In',
    'wifi' => 'WiFi',
    'pay_us' => 'Pay Us',
    'travel_destinations' => 'Travel Destinations',
    'compass' => 'Digital Compass'
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($allItems as $item => $name) {
        $isVisible = isset($_POST[$item]) ? 1 : 0;
        
        // Check if record exists
        $checkSql = "SELECT id FROM item_visibility WHERE hotel_id = $hotelId AND item_name = '$item'";
        $checkResult = $conn->query($checkSql);
        
        if ($checkResult->num_rows > 0) {
            // Update existing record
            $updateSql = "UPDATE item_visibility SET is_visible = $isVisible WHERE hotel_id = $hotelId AND item_name = '$item'";
            $conn->query($updateSql);
        } else {
            // Insert new record
            $insertSql = "INSERT INTO item_visibility (hotel_id, item_name, is_visible) VALUES ($hotelId, '$item', $isVisible)";
            $conn->query($insertSql);
        }
    }
    
    $_SESSION['message'] = "Item visibility updated successfully!";
    header("Location: item-control.php?hotel_id=$hotelId");
    exit();
}
include_once"layouts/header.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Item Visibility Control</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .toggle-btn {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 30px;
        }
        .toggle-btn input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 22px;
            width: 22px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .slider {
            background-color: #28a745;
        }
        input:checked + .slider:before {
            transform: translateX(30px);
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Item Visibility Control</h2>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="row">
                <?php foreach ($allItems as $item => $name): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0"><?php echo $name; ?></h5>
                                <label class="toggle-btn">
                                    <input type="checkbox" name="<?php echo $item; ?>" 
                                        <?php echo (isset($currentStatuses[$item]) && $currentStatuses[$item]) ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <button type="submit" class="btn btn-primary mt-3">Save Changes</button>
            <a href="landing_page.php?id=<?php echo $hotelId; ?>" class="btn btn-secondary mt-3">Back to Landing Page</a>
        </form>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php include_once "layouts/footer.php"; ?>