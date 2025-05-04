<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: auth_system/login.php');
    exit();
}
?>
<?php
include 'layouts/header.php';
require_once 'config.php';

// Check if connection exists
if (!isset($conn) || $conn->connect_error) {
    try {
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
    } catch (Exception $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

// Pagination variables
$perPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page > 1) ? ($page * $perPage) - $perPage : 0;

// Search functionality
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$searchCondition = $search ? "WHERE hotel_name LIKE '%$search%' OR location LIKE '%$search%'" : '';

// Get total rows for pagination
$totalQuery = "SELECT COUNT(*) as total FROM hotels $searchCondition";
$totalResult = $conn->query($totalQuery);
$totalRows = $totalResult->fetch_assoc()['total'];
$pages = ceil($totalRows / $perPage);

// Fetch hotels from database with pagination and search
try {
    $sql = "SELECT * FROM hotels $searchCondition ORDER BY created_at DESC LIMIT $start, $perPage";
    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Error fetching hotels: " . $conn->error);
    }
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}

// Function to get plan type name
function getPlanType($type) {
    switch($type) {
        case 1: return 'Basic';
        case 2: return 'Advance';
        case 3: return 'Premium';
        default: return 'Unknown';
    }
}
?>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Luxury Stays | Our Hotels</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --text-color: #2c3e50;
            --light-gray: #f8f9fa;
            --dark-gray: #333;
            --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f7fa;
            color: var(--text-color);
            line-height: 1.6;
            width: 100%;
            overflow-x: hidden;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        .main-content {
            flex: 1;
            width: 100%;
            background: #f5f7fa;
        }

        .container {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            /* padding: 0 15px; */
        }

        .page-header {
            text-align: center;
            margin-bottom: 2rem;
            padding: 2.5rem 1rem;
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            position: relative;
            overflow: hidden;
            width: 100%;
        }

        .page-header h1 {
            font-size: clamp(1.8rem, 4vw, 2.8rem);
            margin-bottom: 0.8rem;
            font-weight: 600;
            position: relative;
            text-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .page-header p {
            color: rgba(255,255,255,0.9);
            font-size: clamp(1rem, 2vw, 1.2rem);
            max-width: 700px;
            margin: 0 auto;
            position: relative;
        }

        .page-header i {
            margin-right: 0.8rem;
            color: #fff;
        }

        .search-container {
            margin: 2rem 0;
            display: flex;
            justify-content: center;
        }

        .search-box {
            position: relative;
            max-width: 600px;
            width: 100%;
        }

        .search-input {
            width: 100%;
            padding: 0.8rem 1.5rem;
            padding-right: 3rem;
            border-radius: 50px;
            border: 1px solid #ddd;
            font-size: 1rem;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }

        .search-button {
            position: absolute;
            right: 0.5rem;
            top: 50%;
            transform: translateY(-50%);
            background: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 2.5rem;
            height: 2.5rem;
            cursor: pointer;
            transition: var(--transition);
        }

        .search-button:hover {
            background: var(--primary-color);
        }

        .hotel-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
            width: 100%;
        }

        .hotel-card {
            background: #fff;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--box-shadow);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            width: 100%;
        }

        .hotel-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .hotel-image {
            height: 220px;
            overflow: hidden;
            position: relative;
            width: 100%;
        }

        .hotel-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: all 0.6s cubic-bezier(0.215, 0.61, 0.355, 1);
        }

        .hotel-card:hover .hotel-image img {
            transform: scale(1.05);
        }

        .hotel-plan {
            position: absolute;
            top: 0.8rem;
            left: 0.8rem;
            background: rgba(52, 152, 219, 0.9);
            color: #fff;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            z-index: 2;
            font-weight: 500;
        }

        .visit-count {
            position: absolute;
            bottom: 0.8rem;
            left: 0.8rem;
            background: rgba(40, 167, 69, 0.9);
            color: #fff;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            z-index: 2;
            display: flex;
            align-items: center;
            font-weight: 500;
        }

        .visit-count i {
            margin-right: 0.3rem;
        }

        .hotel-info {
            padding: 1.5rem;
        }

        .hotel-name {
            font-size: 1.25rem;
            color: var(--primary-color);
            margin-bottom: 0.8rem;
            font-weight: 600;
            line-height: 1.3;
        }

        .hotel-location, .hotel-contact {
            display: flex;
            align-items: center;
            margin-bottom: 0.7rem;
            color: #666;
            font-size: 0.9rem;
        }

        .hotel-location i, .hotel-contact i {
            margin-right: 0.6rem;
            color: var(--secondary-color);
            width: 20px;
            text-align: center;
            font-size: 0.9rem;
        }

        .hotel-price {
            font-size: 1.1rem;
            color: var(--accent-color);
            font-weight: 600;
            margin: 0.8rem 0;
        }

        .hotel-price span {
            font-size: 0.8rem;
            color: #666;
            font-weight: normal;
        }

        .hotel-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .view-details-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--secondary-color);
            color: #fff;
            padding: 0.7rem;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s;
            font-weight: 500;
            border: none;
            cursor: pointer;
            width: 100%;
            font-size: 0.9rem;
            flex: 1;
        }

        .view-details-btn:hover {
            background: var(--primary-color);
            transform: translateY(-2px);
        }

        .view-details-btn i {
            margin-right: 0.5rem;
        }

        /* Table Styles */
        .hotel-table-container {
            width: 100%;
            overflow-x: auto;
            margin-bottom: 2rem;
            border-radius: 10px;
            box-shadow: var(--box-shadow);
            display: none;
        }

        .hotel-table {
            width: 100%;
            min-width: 600px;
            background: #fff;
            border-collapse: collapse;
        }

        .hotel-table th {
            background: var(--primary-color);
            padding: 1rem;
            font-weight: 500;
            color: #fff;
            text-align: left;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .hotel-table td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
            font-size: 0.9rem;
        }

        .hotel-table tr:last-child td {
            border-bottom: none;
        }

        .hotel-table tr:hover td {
            background: rgba(52, 152, 219, 0.05);
        }

        .table-hotel-info {
            display: flex;
            align-items: center;
        }

        .table-image {
            width: 80px;
            height: 60px;
            border-radius: 8px;
            overflow: hidden;
            margin-right: 1rem;
            flex-shrink: 0;
        }

        .table-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .table-hotel-details h4 {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.3rem;
            font-size: 0.95rem;
        }

        .table-hotel-details p {
            color: #666;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            margin-bottom: 0.2rem;
        }

        .table-hotel-details p i {
            margin-right: 0.5rem;
            color: var(--secondary-color);
            width: 16px;
            text-align: center;
        }

        .table-plan-badge {
            display: inline-block;
            background: rgba(52, 152, 219, 0.1);
            color: var(--secondary-color);
            padding: 0.3rem 0.6rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
            margin-top: 0.3rem;
        }

        .table-actions {
            display: flex;
            gap: 0.5rem;
        }

        .table-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--secondary-color);
            color: #fff;
            padding: 0.6rem 1rem;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.85rem;
            transition: all 0.3s;
            font-weight: 500;
            border: none;
            cursor: pointer;
        }

        .table-btn:hover {
            background: var(--primary-color);
            transform: translateY(-2px);
        }

        .table-btn i {
            margin-right: 0.4rem;
        }

        /* No Hotels Styles */
        .no-hotels {
            text-align: center;
            padding: 3rem 2rem;
            background: #fff;
            border-radius: 15px;
            box-shadow: var(--box-shadow);
            grid-column: 1 / -1;
            width: 100%;
            margin: 0 auto;
        }

        .no-hotels-icon {
            font-size: 2.5rem;
            color: var(--secondary-color);
            margin-bottom: 1rem;
        }

        .no-hotels h3 {
            font-size: 1.4rem;
            color: var(--primary-color);
            margin-bottom: 0.8rem;
        }

        .no-hotels p {
            color: #666;
            font-size: 1rem;
            margin-bottom: 1.5rem;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .explore-btn {
            display: inline-block;
            background: var(--secondary-color);
            color: #fff;
            padding: 0.7rem 1.8rem;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .explore-btn:hover {
            background: var(--primary-color);
            transform: translateY(-2px);
        }

        /* Pagination Styles */
        .pagination-container {
            display: flex;
            justify-content: center;
            margin: 2rem 0;
            width: 100%;
        }

        .pagination {
            display: flex;
            list-style: none;
            padding: 0;
            margin: 0;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.3rem;
        }

        .pagination li {
            margin: 0;
        }

        .pagination a, .pagination span {
            display: inline-block;
            padding: 0.5rem 0.9rem;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.3s;
            font-size: 0.9rem;
            min-width: 40px;
            text-align: center;
        }

        .pagination a {
            color: var(--primary-color);
            border: 1px solid #ddd;
            background: #fff;
        }

        .pagination a:hover {
            background: var(--secondary-color);
            color: white;
            border-color: var(--secondary-color);
        }

        .pagination .active a {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .pagination .disabled a {
            color: #aaa;
            pointer-events: none;
            border-color: #eee;
            background: #f9f9f9;
        }

        /* Responsive Adjustments */
        @media (max-width: 1199px) {
            .hotel-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            }
        }

        @media (max-width: 991px) {
            .hotel-image {
                height: 200px;
            }
            
            .hotel-info {
                padding: 1.2rem;
            }
        }

        @media (max-width: 767px) {
            .hotel-grid {
                display: none;
            }
            
            .hotel-table-container {
                display: block;
            }
            
            .page-header {
                padding: 2rem 1rem;
            }
            
            .search-container {
                margin: 1.5rem 0;
            }
        }

        @media (min-width: 768px) {
            .hotel-grid {
                display: grid;
            }
            
            .hotel-table-container {
                display: none;
            }
        }

        @media (max-width: 575px) {
            .page-header {
                padding: 1.8rem 1rem;
            }
            
            .page-header h1 {
                font-size: 1.8rem;
            }
            
            .search-input {
                padding: 0.7rem 1.2rem;
                padding-right: 2.5rem;
                font-size: 0.9rem;
            }
            
            .search-button {
                width: 2.2rem;
                height: 2.2rem;
            }
            
            .table-image {
                width: 70px;
                height: 50px;
            }
            
            .table-btn {
                padding: 0.5rem 0.8rem;
                font-size: 0.8rem;
            }
            
            .pagination a, .pagination span {
                padding: 0.4rem 0.7rem;
                font-size: 0.85rem;
                min-width: 36px;
            }
        }

        @media (max-width: 400px) {
            .table-actions {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .table-btn {
                width: 100%;
            }
        }
    </style>
    <main class="app-main">
    <div class="admin-container">
        <div class="main-content">
            <div class="container">
                <header class="page-header">
                    <h1><i class="fas fa-hotel"></i> Luxury Stays Collection</h1>
                    <p>Discover our exquisite selection of premium hotels and accommodations worldwide</p>
                </header>

                <!-- Search Box -->
                <div class="search-container">
                    <form class="search-box" method="GET" action="">
                        <input type="text" class="search-input" name="search" placeholder="Search hotels by name or location..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="search-button">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>

                <!-- Grid View (shown on larger screens) -->
                <div class="hotel-grid" id="gridView">
                    <?php if ($result->num_rows > 0):
                        while($hotel = $result->fetch_assoc()):
                    ?>
                        <div class="hotel-card">
                            <div class="hotel-image">
                                <?php if (!empty($hotel['image_url'])) { ?>
                                    <img src="<?php echo $hotel['image_url']; ?>" alt="<?php echo htmlspecialchars($hotel['hotel_name']); ?>">
                                <?php } else { ?>
                                    <div style="width:100%;height:100%;background:#eee;display:flex;align-items:center;justify-content:center;">
                                        <i class="fas fa-hotel" style="font-size:2rem;color:#ccc;"></i>
                                    </div>
                                <?php } ?>
                                <div class="hotel-plan">
                                    <?php echo getPlanType($hotel['plan_type']); ?>
                                </div>
                                <div class="visit-count">
                                    <i class="fas fa-eye"></i>
                                    <?php echo htmlspecialchars($hotel['visit_count']); ?>
                                </div>
                            </div>
                            <div class="hotel-info">
                                <h2 class="hotel-name"><?php echo htmlspecialchars($hotel['hotel_name']); ?></h2>
                                <p class="hotel-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo htmlspecialchars($hotel['location']); ?>
                                </p>
                                <p class="hotel-contact">
                                    <i class="fas fa-phone"></i>
                                    <?php echo htmlspecialchars($hotel['phone']); ?>
                                </p>
                                <div class="hotel-price">
                                    ₹<?php echo htmlspecialchars($hotel['price_range']); ?> <span>/ night</span>
                                </div>
                                <div class="hotel-actions">
                                    <a href="hotel_details.php?id=<?php echo $hotel['id']; ?>" class="view-details-btn">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="landing_page.php?id=<?php echo $hotel['id']; ?>" class="view-details-btn">
                                        <i class="fas fa-external-link-alt"></i> Landing
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php
                        endwhile;
                    else: ?>
                        <div class="no-hotels">
                            <div class="no-hotels-icon">
                                <i class="fas fa-hotel"></i>
                            </div>
                            <h3>No Hotels Available</h3>
                            <p>We're currently updating our hotel collection. Please check back later for our premium selections.</p>
                            <a href="hotels.php" class="explore-btn">Reset Search</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Table View (shown on mobile) -->
                <div class="hotel-table-container">
                    <table class="hotel-table" id="tableView">
                        <thead>
                            <tr>
                                <th>Hotel Information</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0):
                                $result->data_seek(0);
                                while($hotel = $result->fetch_assoc()):
                            ?>
                                <tr>
                                    <td>
                                        <div class="table-hotel-info">
                                            <div class="table-image">
                                                <?php if (!empty($hotel['image_url'])) { ?>
                                                    <img src="<?php echo $hotel['image_url']; ?>" alt="<?php echo htmlspecialchars($hotel['hotel_name']); ?>">
                                                <?php } else { ?>
                                                    <div style="width:100%;height:100%;background:#eee;display:flex;align-items:center;justify-content:center;">
                                                        <i class="fas fa-hotel" style="font-size:1.5rem;color:#ccc;"></i>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                            <div class="table-hotel-details">
                                                <h4><?php echo htmlspecialchars($hotel['hotel_name']); ?></h4>
                                                <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($hotel['location']); ?></p>
                                                <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($hotel['phone']); ?></p>
                                                <p><i class="fas fa-rupee-sign"></i> ₹<?php echo htmlspecialchars($hotel['price_range']); ?> per night</p>
                                                <span class="table-plan-badge"><?php echo getPlanType($hotel['plan_type']); ?></span>
                                                <p style="margin-top:0.5rem;"><i class="fas fa-eye"></i> <?php echo htmlspecialchars($hotel['visit_count']); ?> views</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="hotel_details.php?id=<?php echo $hotel['id']; ?>" class="table-btn">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            <a href="landing_page.php?id=<?php echo $hotel['id']; ?>" class="table-btn">
                                                <i class="fas fa-external-link-alt"></i> Landing
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php
                                endwhile;
                            else: ?>
                                <tr>
                                    <td colspan="2">
                                        <div class="no-hotels" style="text-align:center;padding:2rem;width:100%;">
                                            <div class="no-hotels-icon">
                                                <i class="fas fa-hotel"></i>
                                            </div>
                                            <h3>No Hotels Found</h3>
                                            <p>No hotels match your search criteria.</p>
                                            <a href="hotels.php" class="explore-btn">Reset Search</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($result->num_rows > 0 && $pages > 1): ?>
                <div class="pagination-container">
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                            <li><a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">&laquo; Prev</a></li>
                        <?php else: ?>
                            <li class="disabled"><span>&laquo; Prev</span></li>
                        <?php endif; ?>

                        <?php 
                        // Show page numbers with ellipsis
                        $startPage = max(1, $page - 2);
                        $endPage = min($pages, $page + 2);
                        
                        if ($startPage > 1) {
                            echo '<li><a href="?page=1&search='.urlencode($search).'">1</a></li>';
                            if ($startPage > 2) {
                                echo '<li class="disabled"><span>...</span></li>';
                            }
                        }
                        
                        for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <li <?php echo ($i == $page) ? 'class="active"' : ''; ?>>
                                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; 
                        
                        if ($endPage < $pages) {
                            if ($endPage < $pages - 1) {
                                echo '<li class="disabled"><span>...</span></li>';
                            }
                            echo '<li><a href="?page='.$pages.'&search='.urlencode($search).'">'.$pages.'</a></li>';
                        }
                        ?>

                        <?php if ($page < $pages): ?>
                            <li><a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">Next &raquo;</a></li>
                        <?php else: ?>
                            <li class="disabled"><span>Next &raquo;</span></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php include 'layouts/footer.php'; ?>