<?php
require_once 'config.php';

if (!isset($_GET['hotel_id'])) {
    die("Hotel ID is missing.");
}

$hotelId = (int)$_GET['hotel_id'];

// Fetch amenities
$sql = "SELECT * FROM amenities WHERE hotel_id = $hotelId ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hotel Amenities</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f0f2f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease-in-out;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.06);
            background-color: #fff;
        }
        .card:hover {
            transform: scale(1.015);
            box-shadow: 0 10px 22px rgba(0, 0, 0, 0.12);
        }
        .card-img-top {
            height: 200px;
            object-fit: cover;
            background-color: #f9f9f9;
        }
        .card-body h5 {
            font-weight: 600;
            color: #333;
        }
        .card-text {
            font-size: 0.95rem;
            color: #666;
        }
        .badge-featured {
            font-size: 0.75rem;
            background-color: #198754;
            color: #fff;
            margin-left: 8px;
        }
        .section-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #343a40;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="text-center mb-4">
        <h2 class="section-title">✨ Amenities Available in This Hotel</h2>
        <p class="text-muted">Explore the facilities and features offered for your comfort</p>
    </div>

    <?php if ($result->num_rows > 0): ?>
        <div class="row g-4">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <?php if (!empty($row['image_path']) && file_exists($row['image_path'])): ?>
                            <img src="<?php echo $row['image_path']; ?>" class="card-img-top" alt="Amenity Image">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/400x200?text=No+Image" class="card-img-top" alt="No image">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <?php echo htmlspecialchars($row['amenity_name']); ?>
                                <?php if ($row['is_featured']): ?>
                                    <span class="badge badge-featured">Featured</span>
                                <?php endif; ?>
                            </h5>
                            <?php if (!empty($row['description'])): ?>
                                <p class="card-text"><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
                            <?php else: ?>
                                <p class="card-text text-muted">No description provided.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-warning text-center mt-5">
            <i class="fas fa-info-circle me-2"></i> No amenities found for this hotel.
        </div>
    <?php endif; ?>
</div>

</body>
</html>
