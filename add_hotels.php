<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: auth_system/login.php');
    exit();
}

require_once 'config.php';

$page_title = "Add/Edit Hotel";
$error = '';
$success = '';
$hotel = null;
$plan = isset($_GET['plan']) ? intval($_GET['plan']) : 0;

// Create uploads directory if it doesn't exist
$upload_dir = "uploads";
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Check if editing existing hotel
if (isset($_GET['edit'])) {
    $id = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
    if ($id) {
        $stmt = $conn->prepare("SELECT * FROM hotels WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $hotel = $result->fetch_assoc();
            $plan = $hotel['plan_type'] ?? $plan;
        } else {
            header("Location: hotels.php");
            exit();
        }
    } else {
        header("Location: hotels.php");
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input with trim()
    $hotel_name = trim(filter_input(INPUT_POST, 'hotel_name', FILTER_SANITIZE_STRING));
    $location = trim(filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING));
    $description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING));
    $price_range = trim(filter_input(INPUT_POST, 'price_range', FILTER_SANITIZE_STRING));
    $amenities = trim(filter_input(INPUT_POST, 'amenities', FILTER_SANITIZE_STRING));
    $phone = trim(filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING));
    $whatsapp = trim(filter_input(INPUT_POST, 'whatsapp', FILTER_SANITIZE_STRING));
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    $website = trim(filter_input(INPUT_POST, 'website', FILTER_SANITIZE_URL));
    $google_review = trim(filter_input(INPUT_POST, 'google_review', FILTER_SANITIZE_URL));
    $facebook = trim(filter_input(INPUT_POST, 'facebook', FILTER_SANITIZE_URL));
    $instagram = trim(filter_input(INPUT_POST, 'instagram', FILTER_SANITIZE_URL));
    $dining_menu = isset($_POST['dining_menu']) ? implode(',', array_filter(array_map('trim', $_POST['dining_menu']))) : '';
    $plan_type = isset($_POST['plan_type']) ? intval($_POST['plan_type']) : $plan;

    if (empty($hotel_name) || empty($location)) {
        $error = "Hotel name and location are required";
    } else {
        // Initialize file paths
        $image_url = $hotel['image_url'] ?? '';
        $map_background_url = $hotel['google_map_background'] ?? '';
        $logo_url = $hotel['logo_of_hotel'] ?? '';

        // Process hotel image upload
        if (isset($_FILES['hotel_image']) && $_FILES['hotel_image']['error'] === UPLOAD_ERR_OK) {
            $image_url = processFileUpload('hotel_image', $hotel['image_url'] ?? '', $upload_dir);
            if ($image_url === false) {
                $error = "Failed to upload hotel image. Only JPG, PNG, GIF, WEBP allowed (max 4MB).";
            }
        }

        // Process map background upload (for advance and premium plans)
        if (empty($error) && $plan_type >= 2 && isset($_FILES['map_background']) && $_FILES['map_background']['error'] === UPLOAD_ERR_OK) {
            $map_background_url = processFileUpload('map_background', $hotel['google_map_background'] ?? '', $upload_dir);
            if ($map_background_url === false) {
                $error = "Failed to upload map background. Only JPG, PNG, GIF, WEBP allowed (max 4MB).";
            }
        }

        // Process logo upload (for all plans)
        if (empty($error) && isset($_FILES['logo_of_hotel']) && $_FILES['logo_of_hotel']['error'] === UPLOAD_ERR_OK) {
            $logo_url = processFileUpload('logo_of_hotel', $hotel['logo_of_hotel'] ?? '', $upload_dir);
            if ($logo_url === false) {
                $error = "Failed to upload logo. Only JPG, PNG, GIF, WEBP allowed (max 4MB).";
            }
        }

        // Process dining menu uploads for Advance/Premium plans
        if (empty($error) && $plan_type >= 2 && !empty($_FILES['dining_menu_files'])) {
            $uploaded_menu_files = [];
            foreach ($_FILES['dining_menu_files']['name'] as $key => $name) {
                if ($_FILES['dining_menu_files']['error'][$key] === UPLOAD_ERR_OK) {
                    $file_info = [
                        'name' => $name,
                        'type' => $_FILES['dining_menu_files']['type'][$key],
                        'tmp_name' => $_FILES['dining_menu_files']['tmp_name'][$key],
                        'error' => $_FILES['dining_menu_files']['error'][$key],
                        'size' => $_FILES['dining_menu_files']['size'][$key]
                    ];
                    
                    $uploaded_path = processDiningMenuUpload($file_info, $upload_dir);
                    if ($uploaded_path !== false) {
                        $uploaded_menu_files[] = $uploaded_path;
                    } else {
                        $error = "Failed to upload dining menu file. Only JPG, PNG, GIF, WEBP, PDF allowed (max 4MB).";
                        break;
                    }
                }
            }
            
            if (empty($error)) {
                $dining_menu = implode(',', array_filter(array_merge(explode(',', $dining_menu), $uploaded_menu_files)));
            }
        }

        if (empty($error)) {
            if (isset($_GET['edit'])) {
                $stmt = $conn->prepare("UPDATE hotels SET
                    hotel_name=?, location=?, description=?, price_range=?, amenities=?,
                    phone=?, whatsapp=?, email=?, website=?, google_review_link=?,
                    facebook_link=?, instagram_link=?, dining_menu=?, image_url=?,
                    google_map_background=?, logo_of_hotel=?, plan_type=?
                    WHERE id=?");
                $stmt->bind_param(
                    "ssssssssssssssssii",
                    $hotel_name,
                    $location,
                    $description,
                    $price_range,
                    $amenities,
                    $phone,
                    $whatsapp,
                    $email,
                    $website,
                    $google_review,
                    $facebook,
                    $instagram,
                    $dining_menu,
                    $image_url,
                    $map_background_url,
                    $logo_url,
                    $plan_type,
                    $id
                );
            } else {
                $stmt = $conn->prepare("INSERT INTO hotels (
                    hotel_name, location, description, price_range, amenities,
                    phone, whatsapp, email, website, google_review_link,
                    facebook_link, instagram_link, dining_menu, image_url,
                    google_map_background, logo_of_hotel, plan_type
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param(
                    "ssssssssssssssssi",
                    $hotel_name,
                    $location,
                    $description,
                    $price_range,
                    $amenities,
                    $phone,
                    $whatsapp,
                    $email,
                    $website,
                    $google_review,
                    $facebook,
                    $instagram,
                    $dining_menu,
                    $image_url,
                    $map_background_url,
                    $logo_url,
                    $plan_type
                );
            }

            if ($stmt->execute()) {
                $new_id = isset($_GET['edit']) ? $id : $stmt->insert_id;
                header("Location: hotel_details.php?id=" . $new_id);
                exit();
            } else {
                $error = "Database error: " . $stmt->error;
            }
        }
    }
}

function processFileUpload($fieldName, $currentFilePath, $uploadDir)
{
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $maxFileSize = 4 * 1024 * 1024; // 4MB

    $filename = $_FILES[$fieldName]['name'];
    $tmp_name = $_FILES[$fieldName]['tmp_name'];
    $fileSize = $_FILES[$fieldName]['size'];
    $fileType = $_FILES[$fieldName]['type'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    // Verify file extension
    if (!in_array($ext, $allowed)) {
        return false;
    }

    // Verify file size
    if ($fileSize > $maxFileSize) {
        return false;
    }

    // Verify MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $tmp_name);
    finfo_close($finfo);
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($mime, $allowedMimes)) {
        return false;
    }

    // Generate unique filename
    $newname = uniqid($fieldName . '_', true) . '.' . $ext;
    $destination = $uploadDir . '/' . $newname;

    // Delete old file if exists
    if (!empty($currentFilePath) && file_exists($currentFilePath)) {
        @unlink($currentFilePath);
    }

    if (move_uploaded_file($tmp_name, $destination)) {
        return $destination;
    }

    return false;
}

function processDiningMenuUpload($file, $uploadDir)
{
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'];
    $maxFileSize = 4 * 1024 * 1024; // 4MB

    $filename = $file['name'];
    $tmp_name = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileType = $file['type'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    // Verify file extension
    if (!in_array($ext, $allowed)) {
        return false;
    }

    // Verify file size
    if ($fileSize > $maxFileSize) {
        return false;
    }

    // Verify MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $tmp_name);
    finfo_close($finfo);
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
    if (!in_array($mime, $allowedMimes)) {
        return false;
    }

    // Generate unique filename
    $newname = uniqid('menu_', true) . '.' . $ext;
    $destination = $uploadDir . '/' . $newname;

    if (move_uploaded_file($tmp_name, $destination)) {
        return $destination;
    }

    return false;
}

include 'layouts/header.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($_GET['edit']) ? 'Edit' : 'Add'; ?> Hotel - Hotel Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --accent-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }
        
        .app-main {
            padding: 20px 0;
        }
        
        .admin-header {
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .admin-header h1 {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--secondary-color);
            margin-bottom: 0;
        }
        
        .form-container {
            background-color: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .form-section-title {
            font-size: 1.2rem;
            font-weight: 500;
            color: var(--secondary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .required-field::after {
            content: " *";
            color: var(--accent-color);
        }
        
        .plan-badge {
            font-size: 0.8rem;
            padding: 4px 8px;
            border-radius: 4px;
            margin-left: 10px;
            vertical-align: middle;
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
        
        .current-image {
            margin-bottom: 10px;
        }
        
        .dining-menu-item {
            background-color: #f8f9fa;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }
        
        .btn-danger {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
        }
        
        .btn-success {
            background-color: #2ecc71;
            border-color: #2ecc71;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
        }
        
        .form-text {
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        @media (max-width: 768px) {
            .admin-header h1 {
                font-size: 1.5rem;
            }
            
            .form-section-title {
                font-size: 1.1rem;
            }
        }
    </style>
</head>

<body>
    <main class="app-main">
        <div class="container py-4">
            <header class="admin-header">
                <h1 class="d-flex align-items-center">
                    <i class="fas fa-<?php echo isset($_GET['edit']) ? 'edit' : 'plus-circle'; ?> me-2"></i>
                    <?php echo isset($_GET['edit']) ? 'Edit' : 'Add'; ?> Hotel
                    <?php if ($plan > 0): ?>
                        <span class="plan-badge plan-<?php
                        echo $plan == 1 ? 'basic' : ($plan == 2 ? 'advance' : 'premium');
                        ?> ms-2">
                            <?php echo $plan == 1 ? 'Basic' : ($plan == 2 ? 'Advance' : 'Premium'); ?> Plan
                        </span>
                    <?php endif; ?>
                </h1>
            </header>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="form-container">
                <input type="hidden" name="plan_type" value="<?php echo $plan; ?>">

                <!-- Plan Selection Section -->
                <div class="mb-4">
                    <h3 class="form-section-title">
                        <i class="fas fa-star me-2"></i> Hotel Plan
                    </h3>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="plan_type" class="form-label required-field">Select Plan</label>
                            <select class="form-select" id="plan_type" name="plan_type" required>
                                <option value="1" <?php echo ($plan == 1) ? 'selected' : ''; ?>>Basic</option>
                                <option value="2" <?php echo ($plan == 2) ? 'selected' : ''; ?>>Advance</option>
                                <option value="3" <?php echo ($plan == 3) ? 'selected' : ''; ?>>Premium</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Basic Information Section -->
                <div class="mb-4">
                    <h3 class="form-section-title">
                        <i class="fas fa-info-circle me-2"></i> Basic Information
                    </h3>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="hotel_name" class="form-label required-field">Hotel Name</label>
                            <input type="text" class="form-control" id="hotel_name" name="hotel_name"
                                value="<?php echo htmlspecialchars($hotel['hotel_name'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="location" class="form-label required-field">Location</label>
                            <input type="text" class="form-control" id="location" name="location"
                                value="<?php echo htmlspecialchars($hotel['location'] ?? ''); ?>" required>
                        </div>

                        <?php if ($plan >= 2): ?>
                            <div class="col-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description"
                                    rows="4"><?php echo htmlspecialchars($hotel['description'] ?? ''); ?></textarea>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Hotel Details Section -->
                <?php if ($plan >= 2): ?>
                <div class="mb-4">
                    <h3 class="form-section-title">
                        <i class="fas fa-list-alt me-2"></i> Hotel Details
                    </h3>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="price_range" class="form-label">Price Range</label>
                            <input type="text" class="form-control" id="price_range" name="price_range"
                                value="<?php echo htmlspecialchars($hotel['price_range'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="amenities" class="form-label">Amenities (comma separated)</label>
                            <textarea class="form-control" id="amenities" name="amenities"><?php echo htmlspecialchars($hotel['amenities'] ?? ''); ?></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label"><i class="fas fa-utensils me-2"></i> Dining Menus</label>
                            <div id="dining-menu-wrapper">
                                <?php
                                $diningMenus = explode(',', $hotel['dining_menu'] ?? '');
                                $menuCounter = 0;
                                if (!empty($diningMenus[0])) {
                                    foreach ($diningMenus as $menu) {
                                        $menu = trim($menu);
                                        if (!empty($menu)) {
                                            echo '<div class="dining-menu-item mb-3 p-3 border rounded" data-id="' . $menuCounter . '">
                                                <div class="input-group">
                                                    <input type="text" name="dining_menu[]" class="form-control"
                                                           value="' . htmlspecialchars($menu) . '"
                                                           placeholder="Menu URL or file path" readonly>
                                                    <button type="button" class="btn btn-danger" onclick="removeDiningMenu(this)">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>';
                                                
                                                // Show preview if it's an image
                                                if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $menu)) {
                                                    echo '<div class="mt-2">
                                                        <img src="' . htmlspecialchars($menu) . '" style="max-width: 200px; max-height: 150px;" class="img-thumbnail">
                                                    </div>';
                                                } elseif (preg_match('/\.pdf$/i', $menu)) {
                                                    echo '<div class="mt-2">
                                                        <a href="' . htmlspecialchars($menu) . '" target="_blank" class="btn btn-sm btn-info">
                                                            <i class="fas fa-file-pdf"></i> View PDF
                                                        </a>
                                                    </div>';
                                                }
                                                echo '</div>';
                                            $menuCounter++;
                                        }
                                    }
                                }
                                ?>
                            </div>
                            
                            <div class="mt-3">
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="menu_input_type" id="menu_url" value="url" checked>
                                            <label class="form-check-label" for="menu_url">Enter URL</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="menu_input_type" id="menu_file" value="file">
                                            <label class="form-check-label" for="menu_file">Upload File</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div id="menu_url_section">
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" id="new_menu_url" placeholder="Enter menu URL">
                                        <button type="button" class="btn btn-success" onclick="addMenuUrl()">
                                            <i class="fas fa-plus me-1"></i> Add URL
                                        </button>
                                    </div>
                                </div>
                                
                                <div id="menu_file_section" style="display: none;">
                                    <input type="file" class="form-control" id="new_menu_file" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf">
                                    <div class="form-text">Upload images (JPG, PNG, GIF, WEBP) or PDF files (max 4MB each)</div>
                                    <button type="button" class="btn btn-success mt-2" onclick="uploadMenuFile()">
                                        <i class="fas fa-upload me-1"></i> Upload File
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Contact Information -->
                <div class="mb-4">
                    <h3 class="form-section-title">
                        <i class="fas fa-address-card me-2"></i> Contact Information
                    </h3>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="phone" name="phone"
                                value="<?php echo htmlspecialchars($hotel['phone'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="whatsapp" class="form-label">WhatsApp Number</label>
                            <input type="text" class="form-control" id="whatsapp" name="whatsapp"
                                value="<?php echo htmlspecialchars($hotel['whatsapp'] ?? ''); ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label"><i class="fas fa-envelope me-2"></i> Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="<?php echo htmlspecialchars($hotel['email'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="website" class="form-label"><i class="fas fa-globe me-2"></i> Website</label>
                            <input type="url" class="form-control" id="website" name="website"
                                value="<?php echo htmlspecialchars($hotel['website'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <!-- Social Media Links -->
                <div class="mb-4">
                    <h3 class="form-section-title">
                        <i class="fas fa-share-alt me-2"></i> Social Media & Reviews
                    </h3>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="google_review" class="form-label"><i class="fab fa-google me-2"></i> Google Review Link</label>
                            <input type="url" class="form-control" id="google_review" name="google_review"
                                value="<?php echo htmlspecialchars($hotel['google_review_link'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="facebook" class="form-label"><i class="fab fa-facebook me-2"></i> Facebook Page</label>
                            <input type="url" class="form-control" id="facebook" name="facebook"
                                value="<?php echo htmlspecialchars($hotel['facebook_link'] ?? ''); ?>">
                        </div>
                        <div class="col-12">
                            <label for="instagram" class="form-label"><i class="fab fa-instagram me-2"></i> Instagram Profile</label>
                            <input type="url" class="form-control" id="instagram" name="instagram"
                                value="<?php echo htmlspecialchars($hotel['instagram_link'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <!-- Images Section -->
                <div class="mb-4">
                    <h3 class="form-section-title">
                        <i class="fas fa-images me-2"></i> Hotel Images
                    </h3>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label"><i class="fas fa-image me-2"></i> Hotel Image</label>
                            <?php if (!empty($hotel['image_url'])): ?>
                                <div class="current-image">
                                    <img src="<?php echo htmlspecialchars($hotel['image_url']); ?>"
                                        alt="Current hotel image" style="max-width: 200px; max-height: 150px;" class="img-thumbnail">
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="hotel_image" name="hotel_image"
                                accept="image/*">
                            <div class="form-text">Max 4MB (JPG, PNG, GIF, WEBP)</div>
                        </div>

                        <?php if ($plan >= 2): ?>
                            <div class="col-md-4">
                                <label class="form-label"><i class="fas fa-map me-2"></i> Map Background</label>
                                <?php if (!empty($hotel['google_map_background'])): ?>
                                    <div class="current-image">
                                        <img src="<?php echo htmlspecialchars($hotel['google_map_background']); ?>"
                                            alt="Current map background" style="max-width: 200px; max-height: 150px;" class="img-thumbnail">
                                    </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" id="map_background" name="map_background" accept="image/*">
                                <div class="form-text">Max 4MB (JPG, PNG, GIF, WEBP)</div>
                            </div>
                        <?php endif; ?>

                        <div class="col-md-4">
                            <label class="form-label"><i class="fas fa-image me-2"></i> Hotel Logo</label>
                            <?php if (!empty($hotel['logo_of_hotel'])): ?>
                                <div class="current-image">
                                    <img src="<?php echo htmlspecialchars($hotel['logo_of_hotel']); ?>"
                                        alt="Current hotel logo" style="max-width: 200px; max-height: 150px;" class="img-thumbnail">
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="logo_of_hotel" name="logo_of_hotel"
                                accept="image/*">
                            <div class="form-text">Max 4MB (JPG, PNG, GIF, WEBP)</div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                    <a href="hotels.php" class="btn btn-danger">
                        <i class="fas fa-times me-2"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> <?php echo isset($_GET['edit']) ? 'Update' : 'Add'; ?> Hotel
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle radio button toggle
        document.querySelectorAll('input[name="menu_input_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.getElementById('menu_url_section').style.display = 
                    this.value === 'url' ? 'block' : 'none';
                document.getElementById('menu_file_section').style.display = 
                    this.value === 'file' ? 'block' : 'none';
            });
        });

        function addMenuUrl() {
            const urlInput = document.getElementById('new_menu_url');
            const url = urlInput.value.trim();
            
            if (url) {
                // Create a new menu item element
                const wrapper = document.getElementById('dining-menu-wrapper');
                const newId = Date.now();
                
                const newItem = document.createElement('div');
                newItem.className = 'dining-menu-item mb-3 p-3 border rounded';
                newItem.dataset.id = newId;
                newItem.innerHTML = `
                    <div class="input-group">
                        <input type="text" name="dining_menu[]" class="form-control"
                               value="${url}" placeholder="Menu URL or file path" readonly>
                        <button type="button" class="btn btn-danger" onclick="removeDiningMenu(this)">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
                
                wrapper.appendChild(newItem);
                urlInput.value = '';
            } else {
                alert('Please enter a valid URL');
            }
        }

        function uploadMenuFile() {
            const fileInput = document.getElementById('new_menu_file');
            if (fileInput.files.length > 0) {
                // In a real implementation, you would upload the file via AJAX here
                // For this example, we'll simulate it by creating a preview
                
                const file = fileInput.files[0];
                const fileName = file.name;
                const fileType = file.type;
                
                // Create a temporary object URL for preview
                const objectUrl = URL.createObjectURL(file);
                
                const wrapper = document.getElementById('dining-menu-wrapper');
                const newId = Date.now();
                
                const newItem = document.createElement('div');
                newItem.className = 'dining-menu-item mb-3 p-3 border rounded';
                newItem.dataset.id = newId;
                
                let previewContent = '';
                if (fileType.startsWith('image/')) {
                    previewContent = `<img src="${objectUrl}" style="max-width: 200px; max-height: 150px;" class="img-thumbnail">`;
                } else if (fileName.toLowerCase().endsWith('.pdf')) {
                    previewContent = `
                        <a href="${objectUrl}" target="_blank" class="btn btn-sm btn-info mt-2">
                            <i class="fas fa-file-pdf"></i> View PDF (${fileName})
                        </a>
                    `;
                }
                
                newItem.innerHTML = `
                    <div class="input-group">
                        <input type="text" name="dining_menu[]" class="form-control"
                               value="${fileName}" placeholder="Menu URL or file path" readonly>
                        <button type="button" class="btn btn-danger" onclick="removeDiningMenu(this)">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="mt-2">
                        ${previewContent}
                    </div>
                `;
                
                wrapper.appendChild(newItem);
                fileInput.value = '';
            } else {
                alert('Please select a file to upload');
            }
        }

        function removeDiningMenu(button) {
            const item = button.closest('.dining-menu-item');
            if (item) {
                item.remove();
            }
        }

        // Handle plan type change to show/hide relevant sections
        document.getElementById('plan_type').addEventListener('change', function() {
            const planType = parseInt(this.value);
            const descriptionField = document.getElementById('description').closest('.col-12');
            const hotelDetailsSection = document.querySelector('.form-section-title i.fa-list-alt').closest('.mb-4');
            const mapBackgroundField = document.querySelector('label[for="map_background"]').closest('.col-md-4');
            
            if (planType >= 2) {
                descriptionField.style.display = 'block';
                hotelDetailsSection.style.display = 'block';
                mapBackgroundField.style.display = 'block';
            } else {
                descriptionField.style.display = 'none';
                hotelDetailsSection.style.display = 'none';
                mapBackgroundField.style.display = 'none';
            }
        });
    </script>
    <?php include 'layouts/footer.php'; ?>
</body>

</html>