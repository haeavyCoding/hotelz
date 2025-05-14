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

// All possible items with display names and icons
$allItems = [
    'google_review' => ['name' => 'Google Review', 'icon' => 'fab fa-google'],
    'facebook' => ['name' => 'Facebook', 'icon' => 'fab fa-facebook-f'],
    'instagram' => ['name' => 'Instagram', 'icon' => 'fab fa-instagram'],
    'whatsapp' => ['name' => 'Chat with Us', 'icon' => 'fab fa-whatsapp'],
    'phone' => ['name' => 'Call Us', 'icon' => 'fas fa-phone'],
    'local_attractions' => ['name' => 'Find Us', 'icon' => 'fas fa-map-marker-alt'],
    'dining_menu' => ['name' => 'Dining Menu', 'icon' => 'fas fa-utensils'],
    'amenities' => ['name' => 'Amenities', 'icon' => 'fas fa-concierge-bell'],
    'tv_channels' => ['name' => 'TV Guide', 'icon' => 'fas fa-tv'],
    'email' => ['name' => 'Email', 'icon' => 'fas fa-envelope'],
    'wifi' => ['name' => 'WiFi', 'icon' => 'fas fa-wifi'],
    'house_keeping' => ['name' => 'House Keeping', 'icon' => 'fas fa-broom'],
    'check_in' => ['name' => 'Check In', 'icon' => 'fas fa-user-check'],
    'pay_us' => ['name' => 'Pay Us', 'icon' => 'fas fa-credit-card'],
    'offers' => ['name' => 'Offers', 'icon' => 'fas fa-tag'],
    'travel_destinations' => ['name' => 'Travel Destinations', 'icon' => 'fas fa-plane']
];

// Allowed items per plan_type
$planItems = [
    1 => ['google_review', 'facebook', 'instagram', 'whatsapp', 'phone', 'local_attractions'], // Basic
    2 => ['google_review', 'facebook', 'instagram', 'phone', 'whatsapp', 'dining_menu', 
          'local_attractions', 'email', 'amenities', 'tv_channels', 'wifi'], // Advance
    3 => array_keys($allItems) // Premium (all items)
];

$allowedItems = $planItems[$planType] ?? [];

// Fetch current item statuses
$currentStatuses = [];
$sql = "SELECT * FROM item_visibility WHERE hotel_id = $hotelId";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
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
    foreach ($allItems as $item => $data) {
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Item Visibility Control</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --accent-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
        }

        body {
            background-color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
        }
        
        .app-main {
            padding: 20px 0;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .card-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }
        
        .toggle-switch input {
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
            height: 16px;
            width: 16px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .slider {
            background-color: var(--primary-color);
        }
        
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        
        .plan-badge {
            font-size: 0.8rem;
            padding: 4px 8px;
            border-radius: 4px;
            margin-left: 10px;
        }
        
        .plan-basic {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        
        .plan-advance {
            background-color: #e8f5e9;
            color: #388e3c;
        }
        
        .plan-premium {
            background-color: #f3e5f5;
            color: #8e24aa;
        }
        
        .page-title {
            font-weight: 600;
            color: var(--secondary-color);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 8px 20px;
            font-weight: 500;
        }
        
        .btn-outline-secondary {
            padding: 8px 20px;
            font-weight: 500;
        }
        
        .form-container {
            background-color: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
    </style>
</head>
<body>
    <main class="app-main">
        <div class="container">
            <div class="form-container">
                <h1 class="page-title text-center mb-4">
                    <i class="fas fa-sliders-h"></i> Manage Visible Items
                    <span class="plan-badge plan-<?php 
                        echo $planType == 1 ? 'basic' : ($planType == 2 ? 'advance' : 'premium'); 
                    ?>">
                        <?php echo $planType == 1 ? 'Basic Plan' : ($planType == 2 ? 'Advance Plan' : 'Premium Plan'); ?>
                    </span>
                </h1>

                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-success text-center mb-4">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="row g-4">
                        <?php foreach ($allItems as $item => $data): ?>
                            <?php if (in_array($item, $allowedItems)): ?>
                                <div class="col-md-4 col-sm-6">
                                    <div class="card">
                                        <div class="card-body d-flex justify-content-between align-items-center">
                                            <h5 class="card-title">
                                                <i class="<?php echo $data['icon']; ?>"></i>
                                                <?php echo $data['name']; ?>
                                            </h5>
                                            <label class="toggle-switch">
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

                    <div class="text-center mt-5">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-save me-1"></i> Save Changes
                        </button>
                        <a href="landing_page.php?id=<?php echo $hotelId; ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Hotel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add animation to toggle switches
        document.querySelectorAll('.toggle-switch input').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const card = this.closest('.card');
                if (this.checked) {
                    card.style.boxShadow = '0 6px 12px rgba(52, 152, 219, 0.2)';
                    setTimeout(() => {
                        card.style.boxShadow = '0 6px 12px rgba(0, 0, 0, 0.1)';
                    }, 300);
                } else {
                    card.style.boxShadow = '0 6px 12px rgba(231, 76, 60, 0.2)';
                    setTimeout(() => {
                        card.style.boxShadow = '0 6px 12px rgba(0, 0, 0, 0.1)';
                    }, 300);
                }
            });
        });
    </script>
</body>
</html>

<?php include_once "layouts/footer.php"; ?>