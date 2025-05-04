<?php
session_start();

// Check authentication once at the start
if (!isset($_SESSION['is_logedin']) || $_SESSION['is_logedin'] !== true) {
    header('Location: login.php');
    exit();
}

$page_title = "Total-Reviews";
require_once '../config.php';
include 'layouts/header.php';

// Sanitize the input using prepared statements to prevent SQL injection
$id = $_SESSION['hotel_id'];

// Get hotel info using prepared statement
$hotel_sql = "SELECT * FROM hotels WHERE id = ?";
$stmt = mysqli_prepare($conn, $hotel_sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$hotel_query = mysqli_stmt_get_result($stmt);

if (!$hotel_query || mysqli_num_rows($hotel_query) == 0) {
    die("Hotel not found");
}

$hotel = mysqli_fetch_assoc($hotel_query);

// Pagination setup
$per_page = 4;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $per_page;

// Get total reviews count with prepared statement
$total_query = "SELECT COUNT(*) as total FROM reviews WHERE hotel_id = ?";
$stmt = mysqli_prepare($conn, $total_query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$total_result = mysqli_stmt_get_result($stmt);
$total_row = mysqli_fetch_assoc($total_result);
$total = $total_row['total'];
$pages = ceil($total / $per_page);

// Get reviews with pagination using prepared statement
$reviews_sql = "SELECT * FROM reviews WHERE hotel_id = ? ORDER BY created_at DESC LIMIT ?, ?";
$stmt = mysqli_prepare($conn, $reviews_sql);
$stmt->bind_param("iii", $id, $start, $per_page);
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #1cc88a;
            --dark-color: #5a5c69;
            --light-color: #f8f9fc;
            --star-color: #f6c23e;
            --border-color: #e3e6f0;
        }
        
        body {
            background-color: #f8f9fc;
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        .app-main {
            min-height: calc(100vh - 70px);
            padding: 20px 0;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 30px;
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 15px 20px;
        }
        
        .star-rating i {
            color: var(--star-color);
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead {
            background-color: var(--light-color);
        }
        
        .table th {
            border-top: none;
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(78, 115, 223, 0.05);
        }
        
        .search-box {
            margin-bottom: 1.5rem;
        }
        
        .pagination {
            justify-content: center;
            margin-top: 20px;
        }
        
        .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .page-link {
            color: var(--primary-color);
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        /* Modal Styles */
        .modal-xl {
            max-width: 1100px;
        }
        
        .modal-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        
        .modal-content {
            border-radius: 15px;
            border: none;
        }
        
        .topic-card {
            background: #f8f9fc;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid var(--primary-color);
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        .media-thumbnail {
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s ease;
            height: 180px;
            background-color: #f0f0f0;
        }
        
        .media-thumbnail:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        .media-badge {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
        }
        
        .guest-avatar {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        .rating-badge {
            background-color: rgba(78, 115, 223, 0.1);
            color: var(--primary-color);
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 20px;
        }
        
        .feature-badge {
            background-color: rgba(28, 200, 138, 0.1);
            color: var(--secondary-color);
            margin-right: 8px;
            margin-bottom: 8px;
        }
        
        .companion-badge {
            background-color: rgba(108, 117, 125, 0.1);
            color: #6c757d;
            margin-right: 8px;
            margin-bottom: 8px;
        }
        
        @media (max-width: 768px) {
            .media-thumbnail {
                height: 120px;
            }
            
            .guest-avatar {
                width: 100px;
                height: 100px;
            }
        }
        
    </style>
</head>

<body>
    <main class="app-main">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">Guest Reviews</h1>
                <div class="rating-badge">
                    <i class="fas fa-star"></i> <?php echo $average_rating; ?> (<?php echo $total; ?> reviews)
                </div>
            </div>
            
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-white">
                        <i class="fas fa-hotel me-2"></i> <?php echo htmlspecialchars($hotel['hotel_name']); ?>
                    </h6>
                    <div class="search-box">
                        <div class="input-group">
                            <input type="text" id="searchInput" class="form-control form-control-sm"
                                placeholder="Search reviews...">
                            <button class="btn btn-sm btn-primary" type="button" id="searchBtn">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Guest</th>
                                    <th>Rating</th>
                                    <th>Comment Preview</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($reviews)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <i class="far fa-comment-dots fa-3x text-gray-400 mb-3"></i>
                                            <h5 class="text-gray-600">No Reviews Found</h5>
                                            <p class="text-muted">This hotel doesn't have any reviews yet.</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($reviews as $review): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($review['guest_name'] ?? 'Guest'); ?>&background=random&size=32&rounded=true" 
                                                         alt="Guest" class="rounded-circle me-2">
                                                    <?php echo htmlspecialchars($review['guest_name'] ?? 'Anonymous Guest'); ?>
                                                </div>
                                            </td>
                                            <td class="star-rating">
                                                <?php
                                                for ($i = 1; $i <= 5; $i++) {
                                                    echo $i <= $review['overall_rating'] ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                                }
                                                ?>
                                                <span class="ms-2"><?php echo $review['overall_rating']; ?>/5</span>
                                            </td>
                                            <td>
                                                <?php 
                                                $comment = htmlspecialchars($review['experience']);
                                                echo strlen($comment) > 50 ? substr($comment, 0, 50) . '...' : $comment;
                                                ?>
                                            </td>
                                            <td><?php echo date('M j, Y', strtotime($review['created_at'])); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary view-review-btn"
                                                    data-review='<?php echo htmlspecialchars(json_encode($review), ENT_QUOTES); ?>'>
                                                    <i class="fas fa-eye me-1"></i> View
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($pages > 1): ?>
                        <nav aria-label="Page navigation">
                            <ul class="pagination">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?id=<?php echo $id; ?>&page=<?php echo $page - 1; ?>"
                                            aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $pages; $i++): ?>
                                    <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                        <a class="page-link"
                                            href="?id=<?php echo $id; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?id=<?php echo $id; ?>&page=<?php echo $page + 1; ?>"
                                            aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Review Modal -->
    <div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Review Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-md-4 border-end pe-4">
                            <div class="text-center mb-4">
                                <img id="modalGuestImage" src="" alt="Guest" 
                                    class="guest-avatar rounded-circle mb-3">
                                <h3 id="modalGuest" class="h4 mb-2"></h3>
                                <div class="star-rating mb-2" id="modalRating"></div>
                                <p class="text-muted mb-2" id="modalDate"></p>
                                <span class="badge bg-primary" id="modalTripType"></span>
                            </div>
                            
                            <div class="mb-4">
                                <h5 class="mb-3"><i class="fas fa-users me-2"></i>Travel Companions</h5>
                                <div id="modalTravelWith"></div>
                            </div>
                            
                            <div class="mb-4">
                                <h5 class="mb-3"><i class="fas fa-tags me-2"></i>Hotel Features</h5>
                                <div id="modalHotelFeatures"></div>
                            </div>
                            
                            <div class="mb-4">
                                <h5 class="mb-3"><i class="fas fa-star me-2"></i>Ratings</h5>
                                <div class="row">
                                    <div class="col-6 mb-2">
                                        <small>Overall</small>
                                        <div class="star-rating" id="modalOverallRating"></div>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <small>Rooms</small>
                                        <div class="star-rating" id="modalRoomsRating"></div>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <small>Service</small>
                                        <div class="star-rating" id="modalServiceRating"></div>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <small>Location</small>
                                        <div class="star-rating" id="modalLocationRating"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="col-md-8 ps-4">
                            <div class="mb-4">
                                <h5 class="mb-3"><i class="fas fa-comment me-2"></i>Detailed Experience</h5>
                                <p id="modalComment" class="text-break"></p>
                            </div>

                            <div class="mb-4" id="topicsSection">
                                <h5 class="mb-3"><i class="fas fa-list-ul me-2"></i>Additional Topics</h5>
                                <div id="modalTopics" class="row g-3"></div>
                            </div>

                            <div class="mb-4" id="mediaSection">
                                <h5 class="mb-3"><i class="fas fa-images me-2"></i>Media</h5>
                                <div id="modalMedia" class="row g-3"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Close
                    </button>
                    <button type="button" class="btn btn-primary" onclick="printReview()">
                        <i class="fas fa-print me-1"></i> Print
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        // Search functionality
        $('#searchBtn').on('click', function() {
            const searchText = $('#searchInput').val().toLowerCase();
            $('table tbody tr').each(function() {
                const rowText = $(this).text().toLowerCase();
                $(this).toggle(rowText.includes(searchText));
            });
        });
        
        // View review modal
        $('.view-review-btn').on('click', function() {
            const review = JSON.parse($(this).attr('data-review'));
            const modal = $('#reviewModal');
            
            // Basic Information
            $('#modalGuest').text(review.guest_name || 'Anonymous Guest');
            $('#modalDate').text(new Date(review.created_at).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            }));
            
            // Set all rating stars
            function setRating(elementId, rating) {
                const container = $(elementId).empty();
                for (let i = 1; i <= 5; i++) {
                    container.append(i <= rating ? 
                        '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>');
                }
                container.append(` <span class="ms-1">${rating}/5</span>`);
            }
            
            // Set all ratings
            setRating('#modalRating', review.overall_rating);
            setRating('#modalOverallRating', review.overall_rating);
            setRating('#modalRoomsRating', review.rooms_rating);
            setRating('#modalServiceRating', review.service_rating);
            setRating('#modalLocationRating', review.location_rating);
            
            // Travel Information
            $('#modalTripType').text(review.trip_type ? `${review.trip_type} Trip` : 'Not specified');
            const travelWith = $('#modalTravelWith').empty();
            if (review.travel_with) {
                review.travel_with.split(',').forEach(item => {
                    if (item.trim()) {
                        travelWith.append(`<span class="badge companion-badge">${item.trim()}</span>`);
                    }
                });
            } else {
                travelWith.html('<p class="text-muted"><em>No travel companions specified</em></p>');
            }
            
            // Hotel Features
            const hotelFeatures = $('#modalHotelFeatures').empty();
            if (review.hotel_description) {
                review.hotel_description.split(', ').forEach(feature => {
                    if (feature.trim()) {
                        hotelFeatures.append(`<span class="badge feature-badge">${feature.trim()}</span>`);
                    }
                });
            } else {
                hotelFeatures.html('<p class="text-muted"><em>No features specified</em></p>');
            }
            
            // Detailed Experience
            $('#modalComment').html(
                review.experience ? 
                review.experience.replace(/\n/g, '<br>') : 
                '<p class="text-muted"><em>No experience provided</em></p>'
            );
            
            // Additional Topics
            const topicsContainer = $('#modalTopics').empty();
            if (review.topics) {
                try {
                    const topics = JSON.parse(review.topics);
                    let hasTopics = false;
                    
                    Object.entries(topics).forEach(([topic, details]) => {
                        if (details && details.trim()) {
                            hasTopics = true;
                            topicsContainer.append(`
                                <div class="col-12">
                                    <div class="topic-card">
                                        <h6 class="text-primary"><i class="fas fa-comment-alt me-2"></i>${topic}</h6>
                                        <p class="mb-0">${details.replace(/\n/g, '<br>')}</p>
                                    </div>
                                </div>
                            `);
                        }
                    });
                    
                    if (!hasTopics) {
                        $('#topicsSection').hide();
                    } else {
                        $('#topicsSection').show();
                    }
                } catch (e) {
                    console.error('Error parsing topics:', e);
                    $('#topicsSection').hide();
                }
            } else {
                $('#topicsSection').hide();
            }
            
            // Media Handling
            const mediaContainer = $('#modalMedia').empty();
            if (review.media) {
                const mediaFiles = review.media.split(', ');
                let hasMedia = false;
                
                mediaFiles.forEach(mediaPath => {
                    if (mediaPath.trim()) {
                        hasMedia = true;
                        const isImage = /\.(jpg|jpeg|png|gif)$/i.test(mediaPath);
                        
                        mediaContainer.append(`
                            <div class="col-6 col-md-4 col-lg-3">
                                <div class="media-thumbnail">
                                    ${isImage ? 
                                        `<img src="../${mediaPath.trim()}" class="img-fluid h-100 w-100" loading="lazy">` :
                                        `<video class="img-fluid h-100 w-100" controls>
                                            <source src="../${mediaPath.trim()}" type="video/mp4">
                                        </video>`
                                    }
                                    <span class="media-badge">
                                        ${isImage ? 'Photo' : 'Video'}
                                    </span>
                                </div>
                            </div>
                        `);
                    }
                });
                
                if (!hasMedia) {
                    $('#mediaSection').hide();
                } else {
                    $('#mediaSection').show();
                }
            } else {
                $('#mediaSection').hide();
            }
            
            // Guest Image
            const guestImage = $('#modalGuestImage');
            if (review.guest_image) {
                guestImage.attr('src', `../${review.guest_image}`);
            } else {
                const guestName = review.guest_name || 'Guest';
                guestImage.attr('src', `https://ui-avatars.com/api/?name=${encodeURIComponent(guestName)}&background=random&size=150`);
            }
            
            // Show modal
            new bootstrap.Modal(modal).show();
        });
    });
    
    // Print Functionality
    function printReview() {
        const originalContent = $('body').html();
        const modalContent = $('#reviewModal .modal-content').clone();
        
        // Prepare print content
        $('body').empty().append(`
            <div class="container mt-4">
                <h2 class="mb-4">Review Details - <?php echo htmlspecialchars($hotel['hotel_name']); ?></h2>
                ${modalContent.html()}
            </div>
        `);
        
        // Add print styles
        $('head').append(`
            <style>
                @media print {
                    body { background: white; padding: 20px; }
                    .modal-content { box-shadow: none; border: none; }
                    .modal-header { display: block; }
                    .btn-close, .modal-footer { display: none; }
                    .media-thumbnail video { pointer-events: none; }
                }
            </style>
        `);
        
        window.print();
        $('body').html(originalContent);
    }
    </script>
</body>
</html>
<STYle>
    @media print {
    /* Hide unnecessary elements */
    body * {
        visibility: hidden;
        margin: 0 !important;
        padding: 0 !important;
    }

    /* Print specific styles */
    .print-section {
        visibility: visible;
        position: relative;
        width: 100% !important;
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
        box-shadow: none !important;
        border: none !important;
    }

    /* Print header */
    .print-header {
        text-align: center;
        border-bottom: 2px solid #000;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
    }

    .print-header h2 {
        font-family: "Times New Roman", serif;
        font-size: 24pt;
        margin-bottom: 0.5rem;
    }

    .print-date {
        font-size: 11pt;
        color: #666;
    }

    /* Main content styling */
    .print-content {
        font-family: "Calibri", sans-serif;
        font-size: 12pt;
        line-height: 1.6;
        color: #000 !important;
        max-width: 210mm; /* A4 width */
        margin: 0 auto;
    }

    /* Section styling */
    .print-section {
        page-break-inside: avoid;
        margin-bottom: 1.5rem;
    }

    .print-section h3 {
        font-size: 16pt;
        border-bottom: 1px solid #ccc;
        padding-bottom: 0.5rem;
        margin-bottom: 1rem;
        page-break-after: avoid;
    }

    /* Media handling */
    .media-thumbnail {
        height: auto !important;
        max-height: 150px;
        width: auto !important;
        margin: 0.5rem;
        border: 1px solid #ddd !important;
        page-break-inside: avoid;
    }

    .media-caption {
        font-size: 10pt;
        text-align: center;
        margin-bottom: 1rem;
    }

    /* Topic cards */
    .topic-card {
        background: #fff !important;
        border: 1px solid #ddd !important;
        page-break-inside: avoid;
        margin-bottom: 1rem;
        padding: 1rem;
    }

    /* Rating stars */
    .star-rating i {
        color: #000 !important;
        font-size: 14pt;
    }

    /* Utility classes */
    .print-row {
        display: flex !important;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .print-col {
        flex: 1;
        min-width: 0;
    }

    .no-print {
        display: none !important;
    }

    /* Table styling */
    table {
        width: 100% !important;
        border-collapse: collapse;
        margin-bottom: 1rem;
    }

    th, td {
        border: 1px solid #ddd;
        padding: 0.5rem;
        text-align: left;
        font-size: 11pt;
    }

    th {
        background: #f5f5f5 !important;
        color: #000 !important;
    }

    /* Text formatting */
    .text-break {
        word-wrap: break-word;
        hyphens: auto;
        line-height: 1.4;
    }

    /* Page breaks */
    @page {
        margin: 2cm;
        size: A4 portrait;
    }

    .page-break-before {
        page-break-before: always;
    }
}
</STYle>
<?php include 'layouts/footer.php'; ?>