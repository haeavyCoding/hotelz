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
    // Sanitize and validate input
    $hotel_name = filter_input(INPUT_POST, 'hotel_name', FILTER_SANITIZE_STRING);
    $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $price_range = filter_input(INPUT_POST, 'price_range', FILTER_SANITIZE_STRING);
    $amenities = filter_input(INPUT_POST, 'amenities', FILTER_SANITIZE_STRING);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $whatsapp = filter_input(INPUT_POST, 'whatsapp', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $website = filter_input(INPUT_POST, 'website', FILTER_SANITIZE_URL);
    $google_review = filter_input(INPUT_POST, 'google_review', FILTER_SANITIZE_URL);
    $facebook = filter_input(INPUT_POST, 'facebook', FILTER_SANITIZE_URL);
    $instagram = filter_input(INPUT_POST, 'instagram', FILTER_SANITIZE_URL);
    $dining_menu = isset($_POST['dining_menu']) ? implode(',', array_filter($_POST['dining_menu'])) : '';
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
                $error = "Failed to upload hotel image. Only JPG, PNG, GIF allowed (max 5MB).";
            }
        }

        // Process map background upload (for all plans)
        if (empty($error) && isset($_FILES['map_background']) && $_FILES['map_background']['error'] === UPLOAD_ERR_OK) {
            $map_background_url = processFileUpload('map_background', $hotel['google_map_background'] ?? '', $upload_dir);
            if ($map_background_url === false) {
                $error = "Failed to upload map background. Only JPG, PNG, GIF allowed (max 5MB).";
            }
        }

        // Process logo upload (for all plans)
        if (empty($error) && isset($_FILES['logo_of_hotel']) && $_FILES['logo_of_hotel']['error'] === UPLOAD_ERR_OK) {
            $logo_url = processFileUpload('logo_of_hotel', $hotel['logo_of_hotel'] ?? '', $upload_dir);
            if ($logo_url === false) {
                $error = "Failed to upload logo. Only JPG, PNG, GIF allowed (max 5MB).";
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
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $maxFileSize = 5 * 1024 * 1024; // 5MB

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
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif'];
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

include 'layouts/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($_GET['edit']) ? 'Edit' : 'Add'; ?> Hotel - Hotel Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --text-color: #495057;
            --border-radius: 12px;
            --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }


        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            color: var(--text-color);
            line-height: 1.6;
        }

        .admin-header {
            background: white;
            padding: 25px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 30px;
        }

        .admin-header h1 {
            font-family: 'Playfair Display', serif;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .form-container {
            background: white;
            border-radius: var(--border-radius);
            padding: 30px;
            box-shadow: var(--box-shadow);
            margin-bottom: 30px;
        }

        .form-section-title {
            font-family: 'Playfair Display', serif;
            color: var(--primary-color);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .required-field::after {
            content: " *";
            color: var(--accent-color);
        }

        .current-image img {
            max-width: 100%;
            max-height: 200px;
            border-radius: var(--border-radius);
            margin-bottom: 10px;
            border: 1px solid #ddd;
            padding: 5px;
        }

        .dining-menu-item {
            margin-bottom: 10px;
        }

        .btn-primary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }

        .btn-danger {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
        }

        .menu-type-label {
            padding: 6px 12px;
            border: 2px solid #ddd;
            border-radius: 25px;
            cursor: pointer;
            margin-right: 10px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .menu-type-label.active {
            background: var(--secondary-color);
            color: white;
            border-color: var(--secondary-color);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .menu-type-label:hover {
            background: #e9ecef;
        }

        .menu-type {
            display: none;
        }

        .dining-menu-item.active {
            border-color: var(--secondary-color);
            background-color: #f8fbff;
        }
        
        .plan-badge {
            font-size: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            margin-left: 15px;
        }
        .plan-basic {
            background-color: #6c757d;
            color: white;
        }
        .plan-advance {
            background-color: #17a2b8;
            color: white;
        }
        .plan-premium {
            background-color: #ffc107;
            color: #212529;
        }
        
        .disabled-field {
            opacity: 0.6;
            pointer-events: none;
        }
        @media (max-width: 576px) {
      * {
      font-size: 14px;
    } }
    </style>
</head>
<body>
    <main class="app-main">
        <div class="container py-4">
            <header class="admin-header">
                <h1>
                    <i class="fas fa-<?php echo isset($_GET['edit']) ? 'edit' : 'plus-circle'; ?>"></i>
                    <?php echo isset($_GET['edit']) ? 'Edit' : 'Add'; ?> Hotel
                    <?php if ($plan > 0): ?>
                        <span class="plan-badge plan-<?php 
                            echo $plan == 1 ? 'basic' : ($plan == 2 ? 'advance' : 'premium'); 
                        ?>">
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
                
                <!-- Basic Information Section -->
                <div class="mb-4">
                    <h3 class="form-section-title">
                        <i class="fas fa-info-circle"></i> Basic Information
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
                        
                        <div class="col-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description"
                                rows="4"><?php echo htmlspecialchars($hotel['description'] ?? ''); ?></textarea>
                           
                        </div>
                    </div>
                </div>

                <!-- Hotel Details Section -->
                <div class="mb-4">
                    <h3 class="form-section-title">
                        <i class="fas fa-list-alt"></i> Hotel Details
                    </h3>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="price_range" class="form-label">Price Range</label>
                            <input type="text" class="form-control <?php echo $plan == 1 ? 'disabled-field' : ''; ?>" id="price_range" name="price_range"
                                value="<?php echo htmlspecialchars($hotel['price_range'] ?? ''); ?>" <?php echo $plan == 1 ? 'disabled' : ''; ?>>
                            <?php if ($plan == 1): ?>
                                <small class="text-muted">Upgrade to add price range</small>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label for="amenities" class="form-label">Amenities (comma separated)</label>
                            <textarea class="form-control <?php echo $plan == 1 ? 'disabled-field' : ''; ?>" id="amenities"
                                name="amenities" <?php echo $plan == 1 ? 'disabled' : ''; ?>><?php echo htmlspecialchars($hotel['amenities'] ?? ''); ?></textarea>
                            <?php if ($plan == 1): ?>
                                <small class="text-muted">Upgrade to add amenities</small>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($plan > 2): ?>
                        <div class="col-12">
                            <label class="form-label"><i class="fas fa-utensils"></i> Dining Menus</label>
                            <div id="dining-menu-wrapper">
                                <?php
                                $diningMenus = explode(',', $hotel['dining_menu'] ?? '');
                                $menuCounter = 0;
                                if (!empty($diningMenus[0])) {
                                    foreach ($diningMenus as $menu) {
                                        $isUrl = filter_var(trim($menu), FILTER_VALIDATE_URL);
                                        echo '<div class="dining-menu-item mb-3 p-3 border rounded" data-id="' . $menuCounter . '">
                                <div class="mb-3">
                                    <input type="radio" class="menu-type" name="menu_type[' . $menuCounter . ']"
                                           value="url" id="menu_type_' . $menuCounter . '_url"
                                           ' . ($isUrl ? 'checked' : '') . '>
                                    <label for="menu_type_' . $menuCounter . '_url"
                                           class="menu-type-label ' . ($isUrl ? 'active' : '') . '">
                                        <i class="fas fa-link"></i> URL
                                    </label>

                                    <input type="radio" class="menu-type" name="menu_type[' . $menuCounter . ']"
                                           value="text" id="menu_type_' . $menuCounter . '_text"
                                           ' . (!$isUrl ? 'checked' : '') . '>
                                    <label for="menu_type_' . $menuCounter . '_text"
                                           class="menu-type-label ' . (!$isUrl ? 'active' : '') . '">
                                        <i class="fas fa-text"></i> Text
                                    </label>
                                </div>
                                <div class="input-group">
                                    <input type="' . ($isUrl ? 'url' : 'text') . '" name="dining_menu[]"
                                           class="form-control"
                                           value="' . htmlspecialchars(trim($menu)) . '"
                                           placeholder="' . ($isUrl ? 'https://example.com/menu.pdf' : 'Menu description') . '"
                                           required>
                                    <button type="button" class="btn btn-danger" onclick="removeDiningMenu(this)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                              </div>';
                                        $menuCounter++;
                                    }
                                } else {
                                    echo '<div class="dining-menu-item mb-3 p-3 border rounded" data-id="0">
                            <div class="mb-3">
                                <input type="radio" class="menu-type" name="menu_type[0]"
                                       value="url" id="menu_type_0_url" checked>
                                <label for="menu_type_0_url" class="menu-type-label active">
                                    <i class="fas fa-link"></i> URL
                                </label>

                                <input type="radio" class="menu-type" name="menu_type[0]"
                                       value="text" id="menu_type_0_text">
                                <label for="menu_type_0_text" class="menu-type-label">
                                    <i class="fas fa-text"></i> Text
                                </label>
                            </div>
                            <div class="input-group">
                                <input type="url" name="dining_menu[]" class="form-control"
                                       placeholder="https://example.com/menu.pdf" required>
                                <button type="button" class="btn btn-danger" onclick="removeDiningMenu(this)">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                          </div>';
                                    $menuCounter = 1;
                                }
                                ?>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addDiningMenu()">
                                <i class="fas fa-plus"></i> Add Menu
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="mb-4">
                    <h3 class="form-section-title">
                        <i class="fas fa-address-card"></i> Contact Information
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
                            <label for="email" class="form-label"><i class="fas fa-envelope"></i> Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="<?php echo htmlspecialchars($hotel['email'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="website" class="form-label"><i class="fas fa-globe"></i> Website</label>
                            <input type="url" class="form-control" id="website" name="website"
                                value="<?php echo htmlspecialchars($hotel['website'] ?? ''); ?>">
                           
                        </div>
                    </div>
                </div>

                <!-- Social Media Links -->
                <div class="mb-4">
                    <h3 class="form-section-title">
                        <i class="fas fa-share-alt"></i> Social Media & Reviews
                    </h3>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="google_review" class="form-label"><i class="fab fa-google"></i> Google Review Link</label>
                            <input type="url" class="form-control" id="google_review" name="google_review"
                                value="<?php echo htmlspecialchars($hotel['google_review_link'] ?? ''); ?>">
                            
                        </div>
                        <div class="col-md-6">
                            <label for="facebook" class="form-label"><i class="fab fa-facebook"></i> Facebook Page</label>
                            <input type="url" class="form-control" id="facebook" name="facebook"
                                value="<?php echo htmlspecialchars($hotel['facebook_link'] ?? ''); ?>">
                            <?php if ($plan == 1): ?>
                                <small class="text-muted">Upgrade to add Facebook page</small>
                            <?php endif; ?>
                        </div>
                        <div class="col-12">
                            <label for="instagram" class="form-label"><i class="fab fa-instagram"></i> Instagram Profile</label>
                            <input type="url" class="form-control" id="instagram" name="instagram"
                                value="<?php echo htmlspecialchars($hotel['instagram_link'] ?? ''); ?>">
                           
                        </div>
                    </div>
                </div>

                <!-- Images Section -->
                <div class="mb-4">
                    <h3 class="form-section-title">
                        <i class="fas fa-images"></i> Hotel Images
                    </h3>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label"><i class="fas fa-image"></i> Hotel Image</label>
                            <?php if (!empty($hotel['image_url'])): ?>
                                <div class="current-image">
                                    <img src="<?php echo htmlspecialchars($hotel['image_url']); ?>" alt="Current hotel image">
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="hotel_image" name="hotel_image" accept="image/*">
                            <div class="form-text">Max 5MB (JPG, PNG, GIF)</div>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label"><i class="fas fa-map"></i> Map Background</label>
                            <?php if (!empty($hotel['google_map_background'])): ?>
                                <div class="current-image">
                                    <img src="<?php echo htmlspecialchars($hotel['google_map_background']); ?>"
                                        alt="Current map background">
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control <?php echo $plan == 1 ? 'disabled-field' : ''; ?>" id="map_background" name="map_background"
                                accept="image/*" <?php echo $plan == 1 ? 'disabled' : ''; ?>>
                            <div class="form-text">Max 5MB (JPG, PNG, GIF)</div>
                            <?php if ($plan == 1): ?>
                                <small class="text-muted">Upgrade to add map background</small>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><i class="fas fa-image"></i> Hotel Logo</label>
                            <?php if (!empty($hotel['logo_of_hotel'])): ?>
                                <div class="current-image">
                                    <img src="<?php echo htmlspecialchars($hotel['logo_of_hotel']); ?>"
                                        alt="Current hotel logo">
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="logo_of_hotel" name="logo_of_hotel"
                                accept="image/*" >
                            <div class="form-text">Max 5MB (JPG, PNG, GIF)</div>
                            <?php if ($plan == 1): ?>
                                <small class="text-muted">Upgrade to add hotel logo</small>
                            <?php endif; ?>
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
        let menuCounter = <?php echo $menuCounter; ?>;
        
        function addDiningMenu() {
            const wrapper = document.getElementById('dining-menu-wrapper');
            const div = document.createElement('div');
            div.className = 'dining-menu-item mb-3 p-3 border rounded';
            div.setAttribute('data-id', menuCounter);
            div.innerHTML = `
                <div class="mb-3">
                    <input type="radio" class="menu-type" name="menu_type[${menuCounter}]"
                           value="url" id="menu_type_${menuCounter}_url" checked>
                    <label for="menu_type_${menuCounter}_url" class="menu-type-label active">
                        <i class="fas fa-link"></i> URL
                    </label>

                    <input type="radio" class="menu-type" name="menu_type[${menuCounter}]"
                           value="text" id="menu_type_${menuCounter}_text">
                    <label for="menu_type_${menuCounter}_text" class="menu-type-label">
                        <i class="fas fa-text"></i> Text
                    </label>
                </div>
                <div class="input-group">
                    <input type="url" name="dining_menu[]" class="form-control"
                           placeholder="https://example.com/menu.pdf" required>
                    <button type="button" class="btn btn-danger" onclick="removeDiningMenu(this)">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            wrapper.appendChild(div);

            // Add event listeners for new radio buttons
            div.querySelectorAll('.menu-type').forEach(radio => {
                radio.addEventListener('change', function() {
                    const label = this.closest('.dining-menu-item').querySelector(`label[for="${this.id}"]`);
                    const allLabels = this.closest('.dining-menu-item').querySelectorAll('.menu-type-label');

                    allLabels.forEach(l => l.classList.remove('active'));
                    label.classList.add('active');

                    const input = this.closest('.dining-menu-item').querySelector('input[name="dining_menu[]"]');
                    input.type = this.value === 'url' ? 'url' : 'text';
                    input.placeholder = this.value === 'url' ?
                        'https://example.com/menu.pdf' :
                        'Menu description';
                });
            });

            menuCounter++;
        }

        function removeDiningMenu(button) {
            const item = button.closest('.dining-menu-item');
            if (item) {
                item.remove();
            }
        }

        // Initialize existing menu type toggles
        document.querySelectorAll('.menu-type').forEach(radio => {
            radio.addEventListener('change', function() {
                const label = this.closest('.dining-menu-item').querySelector(`label[for="${this.id}"]`);
                const allLabels = this.closest('.dining-menu-item').querySelectorAll('.menu-type-label');

                allLabels.forEach(l => l.classList.remove('active'));
                label.classList.add('active');

                const input = this.closest('.dining-menu-item').querySelector('input[name="dining_menu[]"]');
                input.type = this.value === 'url' ? 'url' : 'text';
                input.placeholder = this.value === 'url' ?
                    'https://example.com/menu.pdf' :
                    'Menu description';
            });
        });
    </script>
    <?php include 'layouts/footer.php'; ?>
</body>
</html>