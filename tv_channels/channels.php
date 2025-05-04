<?php
include_once("../config.php");
$hotel_id = $_GET["hotel_id"];

// Fetch hotel details
$hotel_query = "SELECT * FROM hotels WHERE id = $hotel_id";
$hotel_result = $conn->query($hotel_query);
$hotel = $hotel_result->fetch_assoc();

// Fetch channels ordered by category then name
$sql = "SELECT * FROM tv_channels ORDER BY category, name";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TV Channels | <?php echo $hotel['hotel_name']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #007BFF;
            --secondary-color: #6c757d;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }
        
        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('hotel-bg.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 80px 0;
            margin-bottom: 40px;
            text-align: center;
        }
        
        .hotel-info {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .search-container {
            margin: 20px 0;
            position: relative;
        }
        
        .search-container input {
            padding: 12px 20px;
            border-radius: 50px;
            border: 1px solid #ddd;
            width: 100%;
            font-size: 16px;
            padding-left: 45px;
        }
        
        .search-container i {
            position: absolute;
            left: 15px;
            top: 12px;
            color: var(--secondary-color);
        }
        
        .channel-table {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .category-header {
            background-color: var(--light-color);
            padding: 10px 15px;
            border-left: 5px solid var(--primary-color);
            margin: 20px 0 10px;
            font-weight: 600;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background-color: var(--primary-color);
            color: white;
            padding: 12px;
            text-align: left;
        }
        
        td {
            padding: 10px 12px;
            border-bottom: 1px solid #eee;
        }
        
        tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }
        
        .channel-logo {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
        }
        
        .footer {
            background-color: var(--dark-color);
            color: white;
            padding: 20px 0;
            text-align: center;
            margin-top: 40px;
        }
        
        @media (max-width: 768px) {
            .hero-section {
                padding: 60px 0;
            }
            
            td, th {
                padding: 8px 10px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1><i class="fas fa-tv me-2"></i> TV Channel Guide</h1>
            <p class="lead">Browse through our extensive collection of available channels</p>
        </div>
    </section>

    <div class="container">
        <!-- Hotel Information -->
        <div class="hotel-info">
            <h2><?php echo $hotel['hotel_name']; ?></h2>
            <p><i class="fas fa-map-marker-alt me-2"></i> <?php echo $hotel['location']; ?></p>
            <p><i class="fas fa-phone me-2"></i> <?php echo $hotel['phone']; ?></p>
        </div>

        <!-- Search Box -->
        <div class="search-container">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Search channels by name, category or language...">
        </div>

        <!-- Channels List -->
        <div class="channel-table">
            <?php
            // Check if records exist
            if ($result->num_rows > 0) {
                $current_category = '';
                
                while ($row = $result->fetch_assoc()) {
                    // If new category, display header
                    if ($current_category != $row['category']) {
                        if ($current_category != '') {
                            echo "</table>";
                        }

                        $current_category = $row['category'];
                        echo "<h3 class='category-header'><i class='fas fa-folder me-2'></i> {$current_category}</h3>";
                        echo "<table class='table-responsive'>";
                        echo "<thead>
                                <tr>
                                    <th>#</th>
                                    <th>Channel</th>
                                    <th>Language</th>
                                </tr>
                              </thead>
                              <tbody>";
                    }

                    echo "<tr class='channel-row' data-name='{$row['name']}' data-category='{$row['category']}' data-language='{$row['language']}'>
                            <td>{$row['id']}</td>
                            <td>
                                <img src='channel-logos/{$row['id']}.png' alt='{$row['name']}' class='channel-logo'>
                                {$row['name']}
                            </td>
                            <td>{$row['language']}</td>
                          </tr>";
                }

                echo "</tbody></table>";
            } else {
                echo "<div class='alert alert-info'>No channels found.</div>";
            }
            ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo $hotel['hotel_name']; ?>. All rights reserved.</p>
            <p class="mb-0">For assistance, please contact front desk.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.channel-row');
            
            rows.forEach(row => {
                const name = row.dataset.name.toLowerCase();
                const category = row.dataset.category.toLowerCase();
                const language = row.dataset.language.toLowerCase();
                
                if (name.includes(searchTerm) || category.includes(searchTerm) || language.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Show/hide category headers based on visible rows
            document.querySelectorAll('.category-header').forEach(header => {
                const category = header.textContent.toLowerCase().replace('📂', '').trim();
                const hasVisibleRows = [...rows].some(row => 
                    row.style.display !== 'none' && 
                    row.dataset.category.toLowerCase() === category
                );
                
                header.style.display = hasVisibleRows ? '' : 'none';
                if (header.nextElementSibling) {
                    header.nextElementSibling.style.display = hasVisibleRows ? '' : 'none';
                }
            });
        });
    </script>
</body>
</html>