<?php
require_once 'config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: auth_system/login.php');
    exit();
}

// Get hotel ID from URL
if (!isset($_GET['hotel_id'])) {
    die("Hotel ID not specified");
}
$hotelId = (int)$_GET['hotel_id'];

// Fetch hotel plan_type
$planSql = "SELECT plan_type FROM hotels WHERE id = $hotelId";
$planResult = $conn->query($planSql);
$planType = 1; // Default to basic

if ($planResult && $planResult->num_rows > 0) {
    $row = $planResult->fetch_assoc();
    $planType = (int)$row['plan_type'];
}

// All possible items with display names
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

// Allowed items per plan_type
$planItems = [
    1 => ['google_review', 'facebook', 'instagram', 'whatsapp', 'phone', 'local_attractions'], // Basic
    2 => ['google_review', 'facebook', 'instagram', 'whatsapp', 'phone', 'local_attractions', 
          'dining_menu', 'amenities', 'tv_channels', 'compass'], // Advance
    3 => array_keys($allItems) // Premium (all items)
];

$allowedItems = $planItems[$planType] ?? [];

// Fetch current item statuses
$sql = "SELECT * FROM item_visibility WHERE hotel_id = $hotelId";
$result = $conn->query($sql);
$currentStatuses = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $currentStatuses[$row['item_name']] = $row['is_visible'];
    }
}

// For items that are allowed by plan but not in database, consider them as ON (1)
foreach ($allowedItems as $item) {
    if (!isset($currentStatuses[$item])) {
        $currentStatuses[$item] = 1; // Default to ON for plan items
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($allItems as $item => $name) {
        if (!in_array($item, $allowedItems)) continue;

        $isVisible = isset($_POST[$item]) ? 1 : 0;

        // Check if record exists
        $checkSql = "SELECT id FROM item_visibility WHERE hotel_id = $hotelId AND item_name = '$item'";
        $checkResult = $conn->query($checkSql);

        if ($checkResult->num_rows > 0) {
            $updateSql = "UPDATE item_visibility SET is_visible = $isVisible WHERE hotel_id = $hotelId AND item_name = '$item'";
            $conn->query($updateSql);
        } else {
            $insertSql = "INSERT INTO item_visibility (hotel_id, item_name, is_visible) VALUES ($hotelId, '$item', $isVisible)";
            $conn->query($insertSql);
        }
    }

    $_SESSION['message'] = "Item visibility updated successfully!";
    header("Location: item-control.php?hotel_id=$hotelId");
    exit();
}

include_once "layouts/header.php";
?>

<!-- Rest of your HTML/CSS remains the same -->

<meta charset="UTF-8">
<title>Item Visibility Control</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    body {
        background-color: #f8f9fa;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .card {
        border: none;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s ease;
        border-radius: 15px;
    }
    .card:hover {
        transform: translateY(-5px);
    }
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
        background-color: #ced4da;
        transition: 0.4s;
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
        transition: 0.4s;
        border-radius: 50%;
    }
    input:checked + .slider {
        background-color: #198754;
    }
    input:checked + .slider:before {
        transform: translateX(30px);
    }
    .card-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #333;
    }
    .btn {
        border-radius: 8px;
    }
    h2 {
        font-weight: 700;
        color: #343a40;
    }
</style>

<main class="app-main">
<div class="container mt-5">
    <h2 class="mb-4 text-center">🛠️ Manage Visible Items for This Hotel</h2>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success text-center"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="row g-4">
            <?php foreach ($allItems as $item => $name): ?>
                <?php if (in_array($item, $allowedItems)): ?>
                    <div class="col-sm-6 col-md-4">
                        <div class="card p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0"><?php echo $name; ?></h5>
                                <label class="toggle-btn mb-0">
                                    <input type="checkbox" name="<?php echo $item; ?>" 
                                        <?php echo (isset($currentStatuses[$item]) && $currentStatuses[$item]) ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-4">
            <button type="submit" class="btn btn-success px-4">💾 Save Changes</button>
            <a href="landing_page.php?id=<?php echo $hotelId; ?>" class="btn btn-outline-secondary px-4 ms-2">🔙 Back</a>
        </div>
    </form>
</div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

<?php include_once "layouts/footer.php"; ?>
