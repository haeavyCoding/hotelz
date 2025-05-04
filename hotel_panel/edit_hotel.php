<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: auth_system/login.php');
    exit();
}

require_once '../config.php';

$page_title = "Edit Hotel";
$error = '';
$hotel = null;

// Get hotel ID from URL
$id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
if (!$id) {
    header("Location: hotels.php");
    exit();
}

// Fetch hotel data
$stmt = $conn->prepare("SELECT * FROM hotels WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows !== 1) {
    header("Location: hotels.php");
    exit();
}
$hotel = $result->fetch_assoc();
$plan = $hotel['plan_type'] ?? 0;

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $hotel_name = $conn->real_escape_string($_POST['hotel_name']);
    $location = $conn->real_escape_string($_POST['location']);
    $description = $conn->real_escape_string($_POST['description']);
    $price_range = $conn->real_escape_string($_POST['price_range']);
    $amenities = $conn->real_escape_string($_POST['amenities']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $whatsapp = $conn->real_escape_string($_POST['whatsapp']);
    $email = $conn->real_escape_string($_POST['email']);
    $website = $conn->real_escape_string($_POST['website']);
    $google_review = $conn->real_escape_string($_POST['google_review']);
    $facebook = $conn->real_escape_string($_POST['facebook']);
    $instagram = $conn->real_escape_string($_POST['instagram']);
    
    // Handle dining menus
    $dining_menus = [];
    if (isset($_POST['dining_menu'])) {
        foreach ($_POST['dining_menu'] as $menu) {
            $menu = trim($menu);
            if (!empty($menu)) {
                $dining_menus[] = $conn->real_escape_string($menu);
            }
        }
    }
    $dining_menu = implode(',', $dining_menus);

    // Handle file uploads
    $image_url = $hotel['image_url'];
    $map_background_url = $hotel['google_map_background'];
    $logo_url = $hotel['logo_of_hotel'];

    if (!empty($_FILES['hotel_image']['name'])) {
        $image_url = uploadFile('hotel_image', $hotel['image_url']);
    }
    if (!empty($_FILES['map_background']['name'])) {
        $map_background_url = uploadFile('map_background', $hotel['google_map_background']);
    }
    if (!empty($_FILES['logo_of_hotel']['name'])) {
        $logo_url = uploadFile('logo_of_hotel', $hotel['logo_of_hotel']);
    }

    // Update database
    $sql = "UPDATE hotels SET 
            hotel_name = '$hotel_name',
            location = '$location',
            description = '$description',
            price_range = '$price_range',
            amenities = '$amenities',
            phone = '$phone',
            whatsapp = '$whatsapp',
            email = '$email',
            website = '$website',
            google_review_link = '$google_review',
            facebook_link = '$facebook',
            instagram_link = '$instagram',
            dining_menu = '$dining_menu',
            image_url = '$image_url',
            google_map_background = '$map_background_url',
            logo_of_hotel = '$logo_url'
            WHERE id = $id";

    if ($conn->query($sql)) {
        header("Location: index.php?id=$id");
        exit();
    } else {
        $error = "Error updating hotel: " . $conn->error;
    }
}

function uploadFile($field, $oldFile) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        return $oldFile;
    }

    $file = $_FILES[$field];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) return $oldFile;
    if ($file['size'] > $maxSize) return $oldFile;

    // upload folder one level above current directory
    $uploadDir = dirname(__DIR__) . '/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $newName = uniqid() . '.' . $ext;
    $target = $uploadDir . $newName;

    if (move_uploaded_file($file['tmp_name'], $target)) {
        // delete old file if it exists
        $oldPath = dirname(__DIR__) . '/' . $oldFile;
        if ($oldFile && file_exists($oldPath)) {
            unlink($oldPath);
        }

        // return relative path for saving in DB or usage
        return 'uploads/' . $newName;
    }

    return $oldFile;
}

include 'layouts/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Hotel - Hotel Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --accent-color: #e74c3c;
            --light-bg: #f8f9fa;
            --card-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            --border-radius: 8px;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-bg);
            color: #333;
        }
        
        .page-header {
            background: white;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            margin-bottom: 2rem;
            border-left: 4px solid var(--primary-color);
        }
        
        .page-header h2 {
            color: var(--secondary-color);
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .form-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .form-card h4 {
            color: var(--secondary-color);
            font-weight: 600;
            margin-bottom: 1.2rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--light-bg);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-card h4 i {
            color: var(--primary-color);
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--secondary-color);
        }
        
        .required-field::after {
            content: " *";
            color: var(--accent-color);
        }
        
        .current-image-container {
            position: relative;
            margin-bottom: 1rem;
        }
        
        .current-image {
            max-width: 100%;
            max-height: 150px;
            border-radius: var(--border-radius);
            border: 1px solid #ddd;
            padding: 5px;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .file-upload-label {
            display: block;
            padding: 0.5rem;
            background: var(--light-bg);
            border: 1px dashed #ccc;
            border-radius: var(--border-radius);
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .file-upload-label:hover {
            border-color: var(--primary-color);
            background: rgba(52, 152, 219, 0.1);
        }
        
        .file-upload-label i {
            display: block;
            font-size: 1.5rem;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 0.5rem 1.5rem;
            font-weight: 500;
        }
        
        .btn-outline-secondary {
            padding: 0.5rem 1.5rem;
            font-weight: 500;
        }
        
        .section-divider {
            border-top: 1px solid #eee;
            margin: 1.5rem 0;
        }
        
        /* Dining Menu Styles */
        .dining-menu-item {
            background-color: #f8f9fa;
            transition: all 0.3s ease;
            margin-bottom: 1rem;
            padding: 1rem;
            border-radius: var(--border-radius);
            border: 1px solid #dee2e6;
        }
        
        .dining-menu-item:hover {
            background-color: #f1f1f1;
        }
        
        .menu-type-btn.active {
            background-color: var(--primary-color);
            color: white;
        }
        
        .remove-menu-btn {
            opacity: 0.7;
            transition: opacity 0.3s;
        }
        
        .remove-menu-btn:hover {
            opacity: 1;
        }
        
        .remove-menu-btn:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }
        
        @media (max-width: 768px) {
            .form-card {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <main class="app-main">
        <div class="container py-4">
            <div class="page-header">
                <h2>
                    <i class="fas fa-hotel"></i>
                    Edit Hotel Information
                </h2>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <!-- Basic Info -->
                <div class="form-card">
                    <h4><i class="fas fa-info-circle"></i> Basic Information</h4>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required-field">Hotel Name</label>
                            <input type="text" name="hotel_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($hotel['hotel_name']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required-field">Location</label>
                            <input type="text" name="location" class="form-control" 
                                   value="<?php echo htmlspecialchars($hotel['location']); ?>" required>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="4"><?php 
                                echo htmlspecialchars($hotel['description']); 
                            ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Hotel Details -->
                <div class="form-card">
                    <h4><i class="fas fa-list-alt"></i> Hotel Details</h4>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Price Range</label>
                            <input type="text" name="price_range" class="form-control" 
                                   value="<?php echo htmlspecialchars($hotel['price_range']); ?>">
                            <small class="text-muted">Example: $100 - $200 per night</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Amenities</label>
                            <textarea name="amenities" class="form-control" rows="3"><?php 
                                echo htmlspecialchars($hotel['amenities']); 
                            ?></textarea>
                            <small class="text-muted">Separate amenities with commas</small>
                        </div>
                    </div>
                </div>

                <!-- Dining Menus -->
                <div class="form-card">
                    <h4><i class="fas fa-utensils"></i> Dining Menus</h4>
                    
                    <div id="dining-menus-container">
                        <?php
                        $dining_menus = !empty($hotel['dining_menu']) ? explode(',', $hotel['dining_menu']) : [''];
                        foreach ($dining_menus as $index => $menu): 
                            $is_url = filter_var($menu, FILTER_VALIDATE_URL);
                        ?>
                        <div class="dining-menu-item mb-3" data-index="<?php echo $index; ?>">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-primary menu-type-btn <?php echo $is_url ? 'active' : ''; ?>" data-type="url">
                                        <i class="fas fa-link"></i> URL
                                    </button>
                                    <button type="button" class="btn btn-outline-primary menu-type-btn <?php echo !$is_url ? 'active' : ''; ?>" data-type="text">
                                        <i class="fas fa-align-left"></i> Text
                                    </button>
                                </div>
                                <button type="button" class="btn btn-sm btn-danger remove-menu-btn" <?php echo $index === 0 ? 'disabled' : ''; ?>>
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <div class="input-group">
                                <input type="<?php echo $is_url ? 'url' : 'text'; ?>" 
                                       name="dining_menu[]" 
                                       class="form-control menu-input" 
                                       value="<?php echo htmlspecialchars($menu); ?>" 
                                       placeholder="<?php echo $is_url ? 'https://example.com/menu.pdf' : 'Menu description'; ?>" 
                                       required>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <button type="button" id="add-menu-btn" class="btn btn-sm btn-outline-primary mt-2">
                        <i class="fas fa-plus me-1"></i> Add Menu
                    </button>
                </div>

                <!-- Contact Info -->
                <div class="form-card">
                    <h4><i class="fas fa-address-book"></i> Contact Information</h4>
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control" 
                                   value="<?php echo htmlspecialchars($hotel['phone']); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">WhatsApp Number</label>
                            <input type="text" name="whatsapp" class="form-control" 
                                   value="<?php echo htmlspecialchars($hotel['whatsapp']); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required-field">Email Address</label>
                            <input type="email" name="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($hotel['email']); ?>" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Website</label>
                            <input type="url" name="website" class="form-control" 
                                   value="<?php echo htmlspecialchars($hotel['website']); ?>">
                            <small class="text-muted">Include https://</small>
                        </div>
                    </div>
                </div>

                <!-- Social Media -->
                <div class="form-card">
                    <h4><i class="fas fa-share-alt"></i> Social Media & Reviews</h4>
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Google Review Link</label>
                            <input type="url" name="google_review" class="form-control" 
                                   value="<?php echo htmlspecialchars($hotel['google_review_link']); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Facebook Page</label>
                            <input type="url" name="facebook" class="form-control" 
                                   value="<?php echo htmlspecialchars($hotel['facebook_link']); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Instagram Profile</label>
                            <input type="url" name="instagram" class="form-control" 
                                   value="<?php echo htmlspecialchars($hotel['instagram_link']); ?>">
                        </div>
                    </div>
                </div>

                <!-- Images -->
                <div class="form-card">
                    <h4><i class="fas fa-images"></i> Hotel Images</h4>
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Hotel Image</label>
                            <?php if ($hotel['image_url']): ?>
                                <div class="current-image-container">
                                    <img src="../<?php echo htmlspecialchars($hotel['image_url']); ?>" class="current-image">
                                </div>
                            <?php endif; ?>
                            <label class="file-upload-label">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Choose new hotel image</span>
                                <input type="file" name="hotel_image" class="d-none" accept="image/*">
                            </label>
                            <small class="text-muted">JPG, PNG or GIF (Max 5MB)</small>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Map Background</label>
                            <?php if ($hotel['google_map_background']): ?>
                                <div class="current-image-container">
                                    <img src="../<?php echo htmlspecialchars($hotel['google_map_background']); ?>" class="current-image">
                                </div>
                            <?php endif; ?>
                            <label class="file-upload-label">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Choose new map background</span>
                                <input type="file" name="map_background" class="d-none" accept="image/*">
                            </label>
                            <small class="text-muted">JPG, PNG or GIF (Max 5MB)</small>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Hotel Logo</label>
                            <?php if ($hotel['logo_of_hotel']): ?>
                                <div class="current-image-container">
                                    <img src="../<?php echo htmlspecialchars($hotel['logo_of_hotel']); ?>" class="current-image">
                                </div>
                            <?php endif; ?>
                            <label class="file-upload-label">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Choose new logo</span>
                                <input type="file" name="logo_of_hotel" class="d-none" accept="image/*">
                            </label>
                            <small class="text-muted">JPG, PNG or GIF (Max 5MB)</small>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex justify-content-between mt-4">
                    <a href="hotels.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> Update Hotel
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add new menu item
            document.getElementById('add-menu-btn').addEventListener('click', function() {
                const container = document.getElementById('dining-menus-container');
                const index = container.children.length;
                const newItem = document.createElement('div');
                newItem.className = 'dining-menu-item mb-3';
                newItem.dataset.index = index;
                newItem.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-primary menu-type-btn active" data-type="url">
                                <i class="fas fa-link"></i> URL
                            </button>
                            <button type="button" class="btn btn-outline-primary menu-type-btn" data-type="text">
                                <i class="fas fa-align-left"></i> Text
                            </button>
                        </div>
                        <button type="button" class="btn btn-sm btn-danger remove-menu-btn">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="input-group">
                        <input type="url" name="dining_menu[]" class="form-control menu-input" 
                               placeholder="https://example.com/menu.pdf" required>
                    </div>
                `;
                container.appendChild(newItem);
                
                // Enable all remove buttons except the first one
                updateRemoveButtons();
            });
            
            // Handle menu type switching
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('menu-type-btn')) {
                    const btnGroup = e.target.closest('.btn-group');
                    const menuItem = e.target.closest('.dining-menu-item');
                    const input = menuItem.querySelector('.menu-input');
                    
                    // Toggle active state
                    btnGroup.querySelectorAll('.menu-type-btn').forEach(btn => {
                        btn.classList.remove('active');
                    });
                    e.target.classList.add('active');
                    
                    // Change input type and placeholder
                    const type = e.target.dataset.type;
                    input.type = type;
                    input.placeholder = type === 'url' 
                        ? 'https://example.com/menu.pdf' 
                        : 'Menu description';
                    input.value = ''; // Clear value when changing type
                }
            });
            
            // Handle menu removal
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-menu-btn') || 
                    e.target.closest('.remove-menu-btn')) {
                    const btn = e.target.classList.contains('remove-menu-btn') 
                        ? e.target 
                        : e.target.closest('.remove-menu-btn');
                    
                    if (!btn.disabled) {
                        const menuItem = btn.closest('.dining-menu-item');
                        menuItem.remove();
                        updateRemoveButtons();
                    }
                }
            });
            
            // Update file upload label with selected file name
            document.querySelectorAll('input[type="file"]').forEach(input => {
                input.addEventListener('change', function() {
                    const label = this.closest('.file-upload-label');
                    if (this.files.length > 0) {
                        label.querySelector('span').textContent = this.files[0].name;
                    } else {
                        label.querySelector('span').textContent = 'Choose file';
                    }
                });
            });
            
            function updateRemoveButtons() {
                const container = document.getElementById('dining-menus-container');
                const items = container.querySelectorAll('.dining-menu-item');
                
                items.forEach((item, index) => {
                    const btn = item.querySelector('.remove-menu-btn');
                    if (btn) {
                        btn.disabled = index === 0;
                    }
                });
            }
        });
    </script>
    <?php include 'layouts/footer.php'; ?>
</body>
</html>