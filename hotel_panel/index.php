<?php
session_start();

if (!isset($_SESSION['is_logedin']) || $_SESSION['is_logedin'] !== true) {
    header('Location: login.php');
    exit();
}

$page_title = "Hotel Details";
require_once '../config.php';

// Sanitize the input to prevent SQL injection
$id = $_SESSION['hotel_id'];

// Get hotel information using prepared statement
$hotel_sql = "SELECT * FROM hotels WHERE id = ?";
$stmt = mysqli_prepare($conn, $hotel_sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$hotel_query = mysqli_stmt_get_result($stmt);

if (!$hotel_query || mysqli_num_rows($hotel_query) == 0) {
    die("Hotel not found: " . mysqli_error($conn));
}

$hotel = mysqli_fetch_assoc($hotel_query);

// Get reviews for this hotel using prepared statement
$reviews_sql = "SELECT * FROM reviews WHERE hotel_id = ? ORDER BY created_at DESC";
$stmt = mysqli_prepare($conn, $reviews_sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$reviews_query = mysqli_stmt_get_result($stmt);

$reviews = [];
while ($row = mysqli_fetch_assoc($reviews_query)) {
    $reviews[] = $row;
}

// Calculate average rating
$total_rating = 0;
foreach ($reviews as $review) {
    $total_rating += $review['overall_rating'];
}
$average_rating = count($reviews) > 0 ? round($total_rating / count($reviews), 1) : 0;

include 'layouts/header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($hotel['hotel_name']); ?> - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --info: #4895ef;
            --warning: #f8961e;
            --danger: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
            --border-radius: 10px;
            --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fb;
            color: #495057;
        }

        .app-main {
            min-height: calc(100vh - 120px);
        }

        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)),
                url('../<?php echo htmlspecialchars($hotel['image_url'] ?? 'images/default-hotel.jpg'); ?>');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 8rem 0;
            margin-bottom: 3rem;
            position: relative;
        }

        .hotel-title {
            font-size: 3rem;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .rating-stars {
            color: #FFD700;
            font-size: 1.25rem;
        }

        .section-title {
            font-size: 1.75rem;
            font-weight: 600;
            position: relative;
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .section-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 4px;
            background: var(--primary);
            border-radius: 2px;
        }

        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            margin-bottom: 1.5rem;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .stats-card {
            border-left: 4px solid var(--primary);
        }

        /* Small Box Widgets */
        .small-box {
            border-radius: var(--border-radius);
            position: relative;
            color: white;
            overflow: hidden;
            margin-bottom: 1.5rem;
            height: 100%;
            transition: var(--transition);
        }

        .small-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .small-box .inner {
            padding: 1.5rem;
            position: relative;
            z-index: 2;
        }

        .small-box h3 {
            font-size: 2rem;
            font-weight: 700;
            margin: 0 0 10px 0;
        }

        .small-box .icon {
            position: absolute;
            top: 15px;
            right: 15px;
            z-index: 1;
            font-size: 70px;
            opacity: 0.2;
            transition: var(--transition);
        }

        .small-box:hover .icon {
            opacity: 0.3;
            transform: scale(1.1);
        }

        .small-box .small-box-footer {
            display: block;
            padding: 10px 0;
            color: rgba(255, 255, 255, 0.8);
            background: rgba(0, 0, 0, 0.1);
            text-align: center;
            text-decoration: none;
            transition: var(--transition);
        }

        .small-box .small-box-footer:hover {
            color: white;
            background: rgba(0, 0, 0, 0.15);
        }

        /* Review Card */
        .review-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--box-shadow);
        }

        .reviewer-img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary);
        }

        /* Progress Bars */
        .progress {
            height: 8px;
            border-radius: 4px;
        }

        .progress-bar {
            background-color: var(--primary);
        }

        /* Responsive Adjustments */
        @media (max-width: 992px) {
            .hotel-title {
                font-size: 2.5rem;
            }

            .section-title {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 768px) {
            .hero-section {
                padding: 6rem 0;
            }

            .hotel-title {
                font-size: 2rem;
            }

            .small-box h3 {
                font-size: 1.5rem;
            }

            .small-box .icon {
                font-size: 50px;
            }
        }

        @media (max-width: 576px) {
            .hero-section {
                padding: 4rem 0;
            }

            .hotel-title {
                font-size: 1.75rem;
            }

            .section-title {
                font-size: 1.25rem;
            }
        }
    </style>
</head>

<body>
    <!-- Main Content -->
    <main class="app-main">
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="container text-center">
                <h1 class="hotel-title"><?php echo htmlspecialchars($hotel['hotel_name']); ?></h1>
                <p class="fs-5"><i class="fas fa-map-marker-alt me-2"></i>
                    <?php echo htmlspecialchars($hotel['location']); ?></p>

                <div class="rating-stars my-3">
                    <?php
                    $full_stars = floor($average_rating);
                    $half_star = ($average_rating - $full_stars) >= 0.5;

                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $full_stars) {
                            echo '<i class="fas fa-star"></i>';
                        } elseif ($half_star && $i == $full_stars + 1) {
                            echo '<i class="fas fa-star-half-alt"></i>';
                        } else {
                            echo '<i class="far fa-star"></i>';
                        }
                    }
                    ?>
                    <div class="mt-2 fs-5">
                        <?php echo $average_rating; ?> / 5 (<?php echo count($reviews); ?> reviews) |
                        <?php echo $hotel['visit_count']; ?> visitors
                    </div>
                </div>
            </div>
        </section>

        <div class="container-fluid px-lg-4 px-3">
            <!-- Quick Stats -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="small-box" style="background-color: var(--primary);">
                        <div class="inner">
                            <h3><?php echo count($reviews); ?></h3>
                            <p>Total Reviews</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <a href="total_reviews.php?id=<?php echo $id; ?>" class="small-box-footer">More info <i
                                class="fas fa-arrow-circle-right ms-1"></i></a>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="small-box" style="background-color: var(--success);">
                        <div class="inner">
                            <h3><?php echo $average_rating; ?>/5</h3>
                            <p>Average Rating</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <a href="#" class="small-box-footer">More info <i
                                class="fas fa-arrow-circle-right ms-1"></i></a>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="small-box" style="background-color: var(--warning);">
                        <div class="inner">
                            <h3><?php echo $hotel['visit_count']; ?></h3>
                            <p>Total Visitors</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <a href="#" class="small-box-footer">More info <i
                                class="fas fa-arrow-circle-right ms-1"></i></a>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="small-box" style="background-color: var(--danger);">
                        <div class="inner">
                            <h3><?php echo date('M d, Y', strtotime($hotel['created_at'])); ?></h3>
                            <p>Added On</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <a href="#" class="small-box-footer">More info <i
                                class="fas fa-arrow-circle-right ms-1"></i></a>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Hotel Details -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h3 class="section-title mb-0"><i class="fas fa-info-circle me-2"></i> Hotel Information
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-3"><strong><i class="fas fa-map-marker-alt me-2 text-primary"></i>
                                            Location:</strong>
                                        <?php echo htmlspecialchars($hotel['location']); ?></p>
                                    <p class="mb-3"><strong><i class="fas fa-utensils me-2 text-primary"></i>
                                            Dining:</strong>
                                        <?php echo ucfirst(htmlspecialchars($hotel['dining_menu'])); ?></p>
                                    <p class="mb-3"><strong><i class="fas fa-phone me-2 text-primary"></i>
                                            Contact:</strong>
                                        <?php echo htmlspecialchars($hotel['phone']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-3"><strong><i class="fas fa-envelope me-2 text-primary"></i>
                                            Email:</strong>
                                        <?php echo htmlspecialchars($hotel['email']); ?></p>
                                    <p class="mb-3"><strong><i class="fas fa-globe me-2 text-primary"></i>
                                            Website:</strong>
                                        <a href="<?php echo htmlspecialchars($hotel['website']); ?>" target="_blank"
                                            class="text-primary">
                                            <?php echo htmlspecialchars($hotel['website']); ?>
                                        </a>
                                    </p>
                                </div>
                            </div>

                            <hr class="my-4">

                            <h4 class="mb-3"><i class="fas fa-align-left me-2 text-primary"></i> Description</h4>
                            <p class="lead"><?php echo nl2br(htmlspecialchars($hotel['description'])); ?></p>

                            <h4 class="mt-4 mb-3"><i class="fas fa-check-circle me-2 text-primary"></i> Amenities</h4>
                            <div class="row">
                                <?php
                                $amenities = explode(',', $hotel['amenities']);
                                foreach ($amenities as $amenity) {
                                    echo '<div class="col-md-4 mb-2"><i class="fas fa-check text-primary me-2"></i>' . htmlspecialchars(trim($amenity)) . '</div>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <!-- Reviews Table -->
                    <div class="card">
                        <div class="card-header bg-white">
                            <h3 class="section-title mb-0"><i class="fas fa-comments me-2"></i> Recent Reviews</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Guest</th>
                                            <th>Rating</th>
                                            <th>Comment</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Get only the 5 most recent reviews
                                        $recent_reviews = array_slice($reviews, 0, 5);
                                        foreach ($recent_reviews as $review): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($review['guest_name']); ?></td>
                                                <td>
                                                    <?php
                                                    for ($i = 1; $i <= 5; $i++) {
                                                        echo $i <= $review['overall_rating'] ? '<i class="fas fa-star text-warning"></i>' : '<i class="far fa-star text-warning"></i>';
                                                    }
                                                    ?>
                                                </td>
                                                <td><?php echo substr(htmlspecialchars($review['experience']), 0, 50) . '...'; ?>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($review['created_at'])); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary view-review-btn"
                                                        data-review='<?php echo htmlspecialchars(json_encode($review), ENT_QUOTES); ?>'>
                                                        <i class="fas fa-eye me-1"></i> View
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics and Actions -->
                <div class="col-lg-4">
                    <div class="card stats-card">
                        <div class="card-header bg-white">
                            <h3 class="section-title mb-0"><i class="fas fa-chart-pie me-2"></i> Statistics</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <h5>Rating Distribution</h5>
                                <?php
                                $rating_counts = [0, 0, 0, 0, 0];
                                foreach ($reviews as $review) {
                                    $rating = min(5, max(1, $review['overall_rating']));
                                    $rating_counts[$rating - 1]++;
                                }

                                for ($i = 5; $i >= 1; $i--) {
                                    $count = $rating_counts[$i - 1];
                                    $percentage = count($reviews) > 0 ? ($count / count($reviews)) * 100 : 0;
                                    echo '
                                    <div class="row align-items-center my-2">
                                        <div class="col-2">
                                            <span class="small">' . $i . ' <i class="fas fa-star text-warning"></i></span>
                                        </div>
                                        <div class="col-8">
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar" role="progressbar" style="width: ' . $percentage . '%"></div>
                                            </div>
                                        </div>
                                        <div class="col-2 text-end">
                                            <span class="small">' . $count . '</span>
                                        </div>
                                    </div>';
                                }
                                ?>
                            </div>

                            <hr>

                            <h5 class="mb-3">Recent Activity</h5>
                            <ul class="list-group">
                                <?php
                                foreach ($recent_reviews as $review) {
                                    echo '
                                    <li class="list-group-item border-0 px-0 py-2">
                                        <div class="d-flex align-items-center">
                                            <img src="https://ui-avatars.com/api/?name=' . urlencode($review['guest_name']) . '&background=4e73df&color=fff"
                                                alt="' . htmlspecialchars($review['guest_name']) . '"
                                                class="rounded-circle me-3" width="40">
                                            <div>
                                                <h6 class="mb-0">' . htmlspecialchars($review['guest_name']) . '</h6>
                                                <small class="text-muted">' . date('M d', strtotime($review['created_at'])) . '</small>
                                            </div>
                                            <div class="ms-auto text-warning">
                                                ' . str_repeat('<i class="fas fa-star"></i>', $review['overall_rating']) . '
                                                ' . str_repeat('<i class="far fa-star"></i>', 5 - $review['overall_rating']) . '
                                            </div>
                                        </div>
                                    </li>';
                                }
                                ?>
                            </ul>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card">
                        <div class="card-header bg-white">
                            <h3 class="section-title mb-0"><i class="fas fa-bolt me-2"></i> Quick Actions</h3>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="edit_hotel.php?edit=<?php echo $hotel['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-edit me-2"></i> Edit Hotel
                                </a>
                                <a href="../google_review/index.php?id=<?php echo $hotel['id']; ?>"
                                    class="btn btn-success">
                                    <i class="fas fa-plus me-2"></i> Add Review
                                </a>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- service click show using hotel id  -->
        <?php $id = $_SESSION['hotel_id'];
        $stmt = $conn->prepare("SELECT * FROM hotels WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows !== 1) {
            header("Location: hotels.php");
            exit();
        }

        $hotel = $result->fetch_assoc();

        // Fetch service click data
        $serviceClicks = [];
        $clickStmt = $conn->prepare("SELECT service_name, click_count FROM service_clicks WHERE hotel_id = ? ORDER BY click_count DESC");
        $clickStmt->bind_param("i", $id);
        $clickStmt->execute();
        $clickResult = $clickStmt->get_result();

        while ($row = $clickResult->fetch_assoc()) {
            $serviceClicks[$row['service_name']] = $row['click_count'];
        }
        ?>
        <div class="info-section px-3">
            <h3><i class="fas fa-chart-line"></i> Service Popularity</h3>
            <?php if (!empty($serviceClicks)): ?>
                <div class="popular-services" style="margin-top: 20px;">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Service Name</th>
                                    <th>Clicks</th>
                                    <th>Popularity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $maxClicks = max($serviceClicks);
                                foreach ($serviceClicks as $service => $clicks):
                                    $percentage = $maxClicks > 0 ? ($clicks / $maxClicks) * 100 : 0;
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($service); ?></td>
                                        <td><?php echo $clicks; ?></td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-success" role="progressbar"
                                                    style="width: <?php echo $percentage; ?>%"
                                                    aria-valuenow="<?php echo $clicks; ?>" aria-valuemin="0"
                                                    aria-valuemax="<?php echo $maxClicks; ?>">
                                                    <?php if ($percentage > 30): ?>
                                                        <?php echo round($percentage, 1); ?>%
                                                    <?php endif; ?>
                                                </div>
                                                <?php if ($percentage <= 30): ?>
                                                    <span style="margin-left: 5px;"><?php echo round($percentage, 1); ?>%</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <p>No service click data available yet.</p>
            <?php endif; ?>
        </div>
    </main>

    <!-- Review Modal -->
    <div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="reviewModalLabel">Review Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <img id="reviewerImage" src="" alt="Reviewer" class="reviewer-img mb-3">
                            <h4 id="reviewerName" class="mb-2"></h4>
                            <p id="reviewDate" class="text-muted"></p>
                            <div class="rating-stars mb-3" id="overallRatingStars"></div>
                            <p class="text-primary fw-bold" id="overallRatingValue"></p>
                        </div>
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <span class="d-block fw-bold">Room Quality</span>
                                    <div class="rating-stars" id="roomRatingStars"></div>
                                    <span class="text-primary" id="roomRatingValue"></span>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <span class="d-block fw-bold">Service Quality</span>
                                    <div class="rating-stars" id="serviceRatingStars"></div>
                                    <span class="text-primary" id="serviceRatingValue"></span>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <span class="d-block fw-bold">Location</span>
                                    <div class="rating-stars" id="locationRatingStars"></div>
                                    <span class="text-primary" id="locationRatingValue"></span>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <span class="d-block fw-bold">Value for Money</span>
                                    <div class="rating-stars" id="valueRatingStars"></div>
                                    <span class="text-primary" id="valueRatingValue"></span>
                                </div>
                            </div>

                            <hr>

                            <div class="mb-3">
                                <span class="d-block fw-bold">Trip Type:</span>
                                <span id="tripType"></span>
                            </div>
                            <div class="mb-3">
                                <span class="d-block fw-bold">Travel With:</span>
                                <span id="travelWith"></span>
                            </div>
                            <div class="mb-3">
                                <span class="d-block fw-bold">Review Topics:</span>
                                <span id="reviewTopics"></span>
                            </div>

                            <hr>

                            <div class="mb-3">
                                <span class="d-block fw-bold">Detailed Experience:</span>
                                <p id="detailedExperience" class="mt-2"></p>
                            </div>

                            <div id="reviewImageContainer" class="text-center mt-3 d-none">
                                <img id="reviewImage" src="" alt="Review Image" class="img-fluid rounded">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function () {
            // Review modal handler
            $(document).on('click', '.view-review-btn', function () {
                var review = JSON.parse($(this).attr('data-review'));

                // Set reviewer info
                $('#reviewerName').text(review.guest_name);
                $('#reviewDate').text('Reviewed on ' + new Date(review.created_at).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                }));

                // Set reviewer image
                $('#reviewerImage').attr('src',
                    'https://ui-avatars.com/api/?name=' + encodeURIComponent(review.guest_name) + '&background=4e73df&color=fff&size=200');

                // Set overall rating
                $('#overallRatingValue').text(review.overall_rating + ' / 5');
                $('#overallRatingStars').html(getRatingStars(review.overall_rating));

                // Set category ratings
                $('#roomRatingValue').text(review.room_rating + ' / 5');
                $('#roomRatingStars').html(getRatingStars(review.room_rating));

                $('#serviceRatingValue').text(review.services_rating + ' / 5');
                $('#serviceRatingStars').html(getRatingStars(review.services_rating));

                $('#locationRatingValue').text(review.location_rating + ' / 5');
                $('#locationRatingStars').html(getRatingStars(review.location_rating));

                $('#valueRatingValue').text(review.value_rating + ' / 5');
                $('#valueRatingStars').html(getRatingStars(review.value_rating));

                // Set other details
                $('#tripType').text(review.trip_type || 'Not specified');
                $('#travelWith').text(review.travel_with || 'Not specified');
                $('#reviewTopics').text(review.topics || 'Not specified');
                $('#detailedExperience').text(review.experience || 'No detailed experience provided');

                // Set review image if available
                var imageContainer = $('#reviewImageContainer');
                if (review.image_url) {
                    $('#reviewImage').attr('src', '../' + review.image_url);
                    imageContainer.removeClass('d-none');
                } else {
                    imageContainer.addClass('d-none');
                }

                // Show the modal
                $('#reviewModal').modal('show');
            });

            // Helper function to generate rating stars
            function getRatingStars(rating) {
                let stars = '';
                for (let i = 1; i <= 5; i++) {
                    if (i <= rating) {
                        stars += '<i class="fas fa-star"></i>';
                    } else if (i - 0.5 <= rating) {
                        stars += '<i class="fas fa-star-half-alt"></i>';
                    } else {
                        stars += '<i class="far fa-star"></i>';
                    }
                }
                return stars;
            }
        });
    </script>
</body>

</html>

<?php include 'layouts/footer.php'; ?>