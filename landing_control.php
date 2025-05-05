<div class="services-container">
    <div class="owl-carousel owl-theme">
        <?php
        $planType = $hotel['plan_type'];
        
        // Fetch visibility status for all items
        $visibilitySql = "SELECT item_name, is_visible FROM item_visibility WHERE hotel_id = $id";
        $visibilityResult = $conn->query($visibilitySql);
        $itemVisibility = [];
        
        if ($visibilityResult->num_rows > 0) {
            while ($row = $visibilityResult->fetch_assoc()) {
                $itemVisibility[$row['item_name']] = $row['is_visible'];
            }
        }
        
        // Function to check if item should be displayed
        function shouldDisplayItem($itemName, $itemVisibility) {
            // If no record exists, default to visible
            return !isset($itemVisibility[$itemName]) || $itemVisibility[$itemName];
        }

        // Basic Plan (1)
        if ($planType == 1) {
            ?>
            <!-- Basic Plan Services -->
            <div class="item">
                <?php if (shouldDisplayItem('google_review', $itemVisibility)): ?>
                <a href="track_service.php?hotel_id=<?php echo $id; ?>&service=Google Review&track=<?php echo urlencode(htmlspecialchars($hotel['google_review_link'])); ?>" class="service-item service-card">
                    <i class="fab fa-google"></i>
                    <p>Review Us</p>
                </a>
                <?php endif; ?>
                
                <?php if (shouldDisplayItem('instagram', $itemVisibility)): ?>
                <a href="track_service.php?hotel_id=<?php echo $id; ?>&service=Instagram&track=<?php echo urlencode(htmlspecialchars($hotel['instagram_link'])); ?>" class="service-item service-card">
                    <i class="fab fa-instagram"></i>
                    <p>Follow Us!</p>
                </a>
                <?php endif; ?>
            </div>

            <div class="item">
                <?php if (shouldDisplayItem('facebook', $itemVisibility)): ?>
                <a href="track_service.php?hotel_id=<?php echo $id; ?>&service=Facebook&track=<?php echo urlencode(htmlspecialchars($hotel['facebook_link'])); ?>" class="service-item service-card">
                    <i class="fab fa-facebook-f"></i>
                    <p>Like Us</p>
                </a>
                <?php endif; ?>
                
                <?php if (shouldDisplayItem('whatsapp', $itemVisibility)): ?>
                <a href="track_service.php?hotel_id=<?php echo $id; ?>&service=WhatsApp&track=<?php echo urlencode('https://wa.me/'.htmlspecialchars($hotel['whatsapp'])); ?>" class="service-item service-card">
                    <i class="fab fa-whatsapp"></i>
                    <p>Chat With Us</p>
                </a>
                <?php endif; ?>
            </div>
            <!-- Continue with other items... -->
            <?php
        }
        ?>
    </div>
</div>