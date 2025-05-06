<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: auth_system/login.php');
    exit();
}

require_once 'config.php';

// Database connection
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

// Handle landing page toggle
if (isset($_GET['toggle_landing']) && isset($_GET['id'])) {
    $hotelId = (int) $_GET['id'];
    // Get current status
    $statusQuery = "SELECT landing_page_enabled FROM hotels WHERE id = $hotelId";
    $statusResult = $conn->query($statusQuery);
    if ($statusResult && $statusResult->num_rows > 0) {
        $currentStatus = $statusResult->fetch_assoc()['landing_page_enabled'];
        $newStatus = $currentStatus ? 0 : 1;
        // Update status
        $updateQuery = "UPDATE hotels SET landing_page_enabled = $newStatus WHERE id = $hotelId";
        if (!$conn->query($updateQuery)) {
            die("Update failed: " . $conn->error);
        }
    }
    // Build clean URL for redirect
    $url = strtok($_SERVER['REQUEST_URI'], '?'); // Get base URL
    $params = $_GET;
    unset($params['toggle_landing'], $params['id']); // Remove toggle params
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }
    header("Location: $url");
    exit();
}

// Pagination and search functionality
$perPage = 15;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$start = ($page > 1) ? ($page * $perPage) - $perPage : 0;

$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$searchCondition = $search ? "WHERE hotel_name LIKE '%$search%' OR location LIKE '%$search%'" : '';

// Get total rows
$totalQuery = "SELECT COUNT(*) as total FROM hotels $searchCondition";
$totalResult = $conn->query($totalQuery);
$totalRows = $totalResult->fetch_assoc()['total'];
$pages = ceil($totalRows / $perPage);

// Fetch hotels
try {
    $sql = "SELECT * FROM hotels $searchCondition ORDER BY created_at DESC LIMIT $start, $perPage";
    $result = $conn->query($sql);
    if (!$result) {
        throw new Exception("Error fetching hotels: " . $conn->error);
    }
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}

function getPlanType($type)
{
    switch ($type) {
        case 1:
            return 'Basic';
        case 2:
            return 'Advance';
        case 3:
            return 'Premium';
        default:
            return 'Unknown';
    }
}
include 'layouts/header.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Luxury Stays | Our Hotels</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
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
            padding: 0 15px;
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
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .page-header p {
            color: rgba(255, 255, 255, 0.9);
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

        /* Table Styles */
        .hotel-table-container {
            width: 100%;
            overflow-x: auto;
            margin-bottom: 2rem;
            border-radius: 10px;
            box-shadow: var(--box-shadow);
        }

        .hotel-table {
            width: 100%;
            min-width: 800px;
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
            padding: 0.8rem 1rem;
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

        .table-image {
            width: 120px;
            height: 80px;
            border-radius: 8px;
            overflow: hidden;
            margin-right: 0.5rem;
            flex-shrink: 0;
        }

        .table-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .table-hotel-name {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.2rem;
        }

        .table-hotel-location {
            color: #666;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
        }

        .table-hotel-location i {
            margin-right: 0.5rem;
            color: var(--secondary-color);
        }

        .table-plan-badge {
            display: inline-block;
            background: rgba(52, 152, 219, 0.1);
            color: var(--secondary-color);
            padding: 0.3rem 0.6rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .table-visits {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.85rem;
            color: #666;
        }

        .table-visits i {
            color: var(--secondary-color);
        }

        .table-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .table-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--secondary-color);
            color: #fff;
            padding: 0.5rem 0.8rem;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.8rem;
            transition: all 0.3s;
            font-weight: 500;
            border: none;
            cursor: pointer;
            white-space: nowrap;
        }

        .table-btn:hover {
            background: var(--primary-color);
            transform: translateY(-2px);
        }

        .table-btn i {
            margin-right: 0.4rem;
            font-size: 0.7rem;
        }

        /* Landing Page Toggle Styles */
        .table-landing-btn.on {
            background-color: #28a745;
        }

        .table-landing-btn.off {
            background-color: #dc3545;
        }

        /* No Hotels Styles */
        .no-hotels {
            text-align: center;
            padding: 3rem 2rem;
            background: #fff;
            border-radius: 15px;
            box-shadow: var(--box-shadow);
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

        .pagination a,
        .pagination span {
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
        @media (max-width: 767px) {
            .table-image {
                width: 100px;
                height: 70px;
            }

            .hotel-table th,
            .hotel-table td {
                padding: 0.7rem 0.8rem;
            }

            .table-actions {
                flex-direction: column;
                gap: 0.5rem;
            }

            .table-btn {
                width: 100%;
                justify-content: flex-start;
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
        }
    </style>
</head>

<body>
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
                            <input type="text" class="search-input" name="search"
                                placeholder="Search hotels by name or location..."
                                value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="search-button">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>

                    <!-- Table View -->
                    <div class="hotel-table-container">
                        <table class="hotel-table" id="tableView">
                            <thead>
                                <tr class="text-center">
                                    <th class="text-center">Image</th>
                                    <th class="text-center">Hotel Name</th>
                                    <th class="text-center">Location</th>
                                    <th class="text-center">Contact</th>
                                    <th class="text-center">Plan</th>
                                    <th class="text-center">Visits</th>
                                    <th class="text-center">Landing Page</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result->num_rows > 0):
                                    $result->data_seek(0);
                                    while ($hotel = $result->fetch_assoc()): ?>
                                        <tr class="text-center">
                                            <td>
                                                <div class="table-image">
                                                    <?php if (!empty($hotel['image_url'])) { ?>
                                                        <img src="<?php echo $hotel['image_url']; ?>"
                                                            alt="<?php echo htmlspecialchars($hotel['hotel_name']); ?>">
                                                    <?php } else { ?>
                                                        <div
                                                            style="width:100%;height:100%;background:#eee;display:flex;align-items:center;justify-content:center;">
                                                            <i class="fas fa-hotel" style="font-size:1.5rem;color:#ccc;"></i>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="table-hotel-name">
                                                    <?php echo htmlspecialchars($hotel['hotel_name']); ?></div>
                                            </td>
                                            <td>
                                                <div class="table-hotel-location">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                    <?php echo htmlspecialchars($hotel['location']); ?>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($hotel['phone']); ?></td>
                                            <td><span
                                                    class="table-plan-badge"><?php echo getPlanType($hotel['plan_type']); ?></span>
                                            </td>
                                            <td>
                                                <div class="table-visits">
                                                    <i class="fas fa-eye"></i>
                                                    <?php echo htmlspecialchars($hotel['visit_count']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="?toggle_landing=1&id=<?php echo $hotel['id']; ?>&page=<?php echo $page; ?>&search=<?php echo urlencode($search); ?>"
                                                    class="table-btn table-landing-btn <?php echo $hotel['landing_page_enabled'] ? 'on' : 'off'; ?>"
                                                    title="<?php echo $hotel['landing_page_enabled'] ? 'Disable Landing Page' : 'Enable Landing Page'; ?>">
                                                    <i class="fas fa-power-off"></i>
                                                    <?php echo $hotel['landing_page_enabled'] ? 'ON' : 'OFF'; ?>
                                                </a>
                                            </td>
                                            <td>
                                                <div class="table-actions">
                                                    <a href="hotel_details.php?id=<?php echo $hotel['id']; ?>"
                                                        class="table-btn">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                    <a href="landing_page.php?id=<?php echo $hotel['id']; ?>" class="table-btn text-bg-success">
                                                        <i class="fas fa-external-link-alt"></i> Landing
                                                    </a>
                                                    <a href="item-control.php?hotel_id=<?php echo $hotel['id']; ?>"
                                                        class="table-btn text-bg-warning">
                                                        <i class="fas fa-tools"></i> Control Services
                                                    </a>
                                                    <a href="delete_hotel.php?id=<?php echo $hotel['id']; ?>"
                                                        class="table-btn text-bg-danger">
                                                        <i class="fas fa-trash"></i> delete Hotel
                                                    </a>
                                                </div>
                                            </td>


                                        </tr>
                                    <?php endwhile; else: ?>
                                    <tr>
                                        <td colspan="8">
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
                                    <li><a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">&laquo;
                                            Prev</a></li>
                                <?php else: ?>
                                    <li class="disabled"><span>&laquo; Prev</span></li>
                                <?php endif; ?>

                                <?php
                                $startPage = max(1, $page - 2);
                                $endPage = min($pages, $page + 2);

                                if ($startPage > 1) {
                                    echo '<li><a href="?page=1&search=' . urlencode($search) . '">1</a></li>';
                                    if ($startPage > 2) {
                                        echo '<li class="disabled"><span>...</span></li>';
                                    }
                                }

                                for ($i = $startPage; $i <= $endPage; $i++): ?>
                                    <li <?php echo ($i == $page) ? 'class="active"' : ''; ?>>
                                        <a
                                            href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor;

                                if ($endPage < $pages) {
                                    if ($endPage < $pages - 1) {
                                        echo '<li class="disabled"><span>...</span></li>';
                                    }
                                    echo '<li><a href="?page=' . $pages . '&search=' . urlencode($search) . '">' . $pages . '</a></li>';
                                }
                                ?>

                                <?php if ($page < $pages): ?>
                                    <li><a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">Next
                                            &raquo;</a></li>
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Add confirmation dialog for landing page toggle
            const toggleButtons = document.querySelectorAll('a[href*="toggle_landing"]');
            toggleButtons.forEach(button => {
                button.addEventListener('click', function (e) {
                    const isEnabled = this.classList.contains('on');
                    const hotelName = this.closest('tr').querySelector('.table-hotel-name').textContent;

                    if (!confirm(`Are you sure you want to ${isEnabled ? 'disable' : 'enable'} the landing page for "${hotelName}"?`)) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>

    <?php include 'layouts/footer.php'; ?>
</body>

</html>