<?php
include_once('../config.php');

$review_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($review_id <= 0) {
    die('Invalid review ID');
}

// Function from above to get review details
$review = getReviewDetails($review_id);

if (!$review) {
    die('Review not found');
}

// Display detailed review information
?>
<div class="review-details">
    <h4><?php echo htmlspecialchars($review['hotel_name']); ?></h4>
    <p class="text-muted"><?php echo htmlspecialchars($review['hotel_location']); ?></p>
    <p><small class="text-muted">Posted on: <?php echo date('F j, Y, g:i a', strtotime($review['created_at'])); ?></small></p>
    
    <div class="ratings mb-4">
        <h5>Ratings:</h5>
        <div class="row">
            <div class="col-md-3">
                <strong>Overall:</strong> <?php echo str_repeat('★', $review['overall_rating']) . str_repeat('☆', 5 - $review['overall_rating']); ?>
            </div>
            <div class="col-md-3">
                <strong>Rooms:</strong> <?php echo str_repeat('★', $review['rooms_rating']) . str_repeat('☆', 5 - $review['rooms_rating']); ?>
            </div>
            <div class="col-md-3">
                <strong>Service:</strong> <?php echo str_repeat('★', $review['service_rating']) . str_repeat('☆', 5 - $review['service_rating']); ?>
            </div>
            <div class="col-md-3">
                <strong>Location:</strong> <?php echo str_repeat('★', $review['location_rating']) . str_repeat('☆', 5 - $review['location_rating']); ?>
            </div>
        </div>
    </div>
    
    <div class="experience mb-4">
        <h5>Experience:</h5>
        <p><?php echo nl2br(htmlspecialchars($review['experience'])); ?></p>
    </div>
    
    <div class="trip-info mb-4">
        <div class="row">
            <div class="col-md-6">
                <strong>Trip Type:</strong> <?php echo ucfirst(htmlspecialchars($review['trip_type'])); ?>
            </div>
            <div class="col-md-6">
                <strong>Traveled With:</strong> <?php echo ucfirst(htmlspecialchars($review['travel_with'])); ?>
            </div>
        </div>
    </div>
    
    <?php if (!empty($review['hotel_description'])): ?>
    <div class="hotel-description mb-4">
        <h5>Hotel Descriptions:</h5>
        <p><?php echo htmlspecialchars($review['hotel_description']); ?></p>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($review['topics'])): ?>
    <div class="additional-topics mb-4">
        <h5>Additional Topics:</h5>
        <ul>
            <?php foreach ($review['topics'] as $topic => $details): ?>
                <li><strong><?php echo ucfirst(htmlspecialchars($topic)); ?>:</strong> <?php echo htmlspecialchars($details); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($review['media'])): ?>
    <div class="review-media">
        <h5>Media:</h5>
        <div class="row">
            <?php foreach ($review['media'] as $media_path): ?>
                <?php if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $media_path)): ?>
                    <div class="col-md-4 mb-3">
                        <a href="<?php echo htmlspecialchars($media_path); ?>" data-lightbox="review-media">
                            <img src="<?php echo htmlspecialchars($media_path); ?>" class="img-thumbnail">
                        </a>
                    </div>
                <?php elseif (preg_match('/\.(mp4|mov|avi)$/i', $media_path)): ?>
                    <div class="col-md-6 mb-3">
                        <video controls class="img-thumbnail" style="width:100%">
                            <source src="<?php echo htmlspecialchars($media_path); ?>" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>