<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: auth_system/login.php');
    exit();
}
?>
<?php
$page_title = "Admin Dashboard";
require_once 'config.php';
include 'layouts/header.php';

// Get statistics
$total_hotels = $conn->query("SELECT COUNT(*) as count FROM hotels")->fetch_assoc()['count'];
$recent_hotels = $conn->query("SELECT * FROM hotels ORDER BY created_at DESC LIMIT 5");
?>

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
    }

    * {
        padding: 0;
        margin: 0;
        box-sizing: border-box;
    }

    .app-main {
        min-height: calc(100vh - 120px);
        padding: 20px;
        background-color: #f5f7fb;
    }

    .app-content {
        width: 100% !important;
    }

    .container-fluid {
        width: 100% !important;
    }

    .app-content-header {
        padding: 15px 0;
        margin-bottom: 30px;
        border-bottom: 1px solid #dee2e6;
    }

    .small-box {
        border-radius: 10px;
        position: relative;
        display: flex;
        flex-direction: column;
        min-width: 0;
        word-wrap: break-word;
        background-clip: border-box;
        border: 1px solid rgba(0, 0, 0, .125);
        margin-bottom: 20px;
        color: white;
        overflow: hidden;
    }

    .small-box .inner {
        padding: 20px;
        position: relative;
        z-index: 2;
    }

    .small-box h3 {
        font-size: 2.2rem;
        font-weight: 700;
        margin: 0 0 10px 0;
        white-space: nowrap;
        padding: 0;
    }

    .small-box p {
        font-size: 1rem;
        margin-bottom: 0;
    }

    .small-box .small-box-icon {
        position: absolute;
        top: 20px;
        right: 20px;
        z-index: 1;
        font-size: 70px;
        opacity: 0.2;
        transition: all 0.3s ease;
    }

    .small-box:hover .small-box-icon {
        opacity: 0.4;
        transform: scale(1.1);
    }

    .small-box-footer {
        display: block;
        padding: 10px 20px;
        color: rgba(255, 255, 255, 0.8);
        background: rgba(0, 0, 0, 0.1);
        text-decoration: none;
        z-index: 2;
        transition: all 0.3s ease;
    }

    .small-box-footer:hover {
        color: white;
        background: rgba(0, 0, 0, 0.2);
    }

    .text-bg-primary {
        background-color: var(--primary);
    }

    .text-bg-success {
        background-color: var(--success);
    }

    .text-bg-warning {
        background-color: var(--warning);
    }

    .text-bg-danger {
        background-color: var(--danger);
    }

    .card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        margin-bottom: 30px;
    }

    .card-header {
        background-color: white;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        padding: 20px;
        border-radius: 10px 10px 0 0 !important;
    }

    .card-title {
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 0;
    }

    /* Improved Table Responsiveness */
    .table-responsive {
        border-radius: 10px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        position: relative;
    }

    .table {
        width: 100%;
        margin-bottom: 0;
        white-space: nowrap;
    }

    .table th {
        background-color: #f8f9fa;
        border-top: none;
        font-weight: 600;
    }

    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        border-radius: 5px;
    }

    .btn-primary {
        background-color: var(--primary);
        border-color: var(--primary);
    }

    .btn-danger {
        background-color: var(--danger);
        border-color: var(--danger);
    }

    .chart-container {
        height: 300px;
        width: 100%;
    }

    .breadcrumb {
        background-color: transparent;
        padding: 0;
        margin-bottom: 0;
    }

    .breadcrumb-item a {
        color: var(--primary);
        text-decoration: none;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {

        .table th,
        .table td {
            padding: 0.5rem;
        }

        .btn-group .btn {
            padding: 0.2rem 0.4rem;
            font-size: 0.75rem;
        }
    }

    @media (max-width: 576px) {
        .btn-group {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        * {
            font-size: 14px
        }

        .btn-group .btn {
            width: 100%;
        }

        .small-box h3 {
            font-size: 1.8rem;
        }

        .small-box-icon {
            font-size: 50px;
        }
    }

    /* Optional scroll indicator */
    .table-responsive::after {
        content: '→';
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--primary);
        font-size: 1.5rem;
        opacity: 0.5;
        display: none;
    }

    @media (max-width: 768px) {
        .table-responsive::after {
            display: block;
        }
    }
</style>

<!-- Main Content -->
<main class="app-main">
    <!-- Content Header -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Dashboard Overview</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#"><i class="fas fa-home"></i> Home</a></li>
                        <li class="breadcrumb-item active"><i class="fas fa-tachometer-alt"></i> Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="">
        <div class="">
            <!-- Stats Cards Row -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box text-bg-primary">
                        <div class="inner">
                            <h3><?php echo $total_hotels; ?></h3>
                            <p>Total Hotels</p>
                        </div>
                        <i class="small-box-icon fas fa-hotel"></i>
                        <a href="hotels.php" class="small-box-footer">
                            View All <i class="fas fa-arrow-circle-right ms-1"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box text-bg-success">
                        <div class="inner">
                            <h3><?php echo $total_hotels; ?></h3>
                            <p>Clients ID</p>
                        </div>
                        <i class="small-box-icon fas fa-bed"></i>
                        <a href="clints_id_pass.php" class="small-box-footer">
                            View All <i class="fas fa-arrow-circle-right ms-1"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box text-bg-warning">
                        <div class="inner">
                            <h3>44</h3>
                            <p>New Bookings</p>
                        </div>
                        <i class="small-box-icon fas fa-calendar-check"></i>
                        <a href="#" class="small-box-footer">
                            View Bookings <i class="fas fa-arrow-circle-right ms-1"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box text-bg-danger">
                        <div class="inner">
                            <h3>65</h3>
                            <p>Pending Requests</p>
                        </div>
                        <i class="small-box-icon fas fa-clock"></i>
                        <a href="#" class="small-box-footer">
                            View Requests <i class="fas fa-arrow-circle-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Hotels Table -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h3 class="card-title"><i class="fas fa-hotel"></i> Recently Added Hotels</h3>
                            <a href="plans.php" class="ms-3 btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i> Add New Hotel
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th><i class="fas fa-hotel me-1"></i> Hotel Name</th>
                                            <th><i class="fas fa-map-marker-alt me-1"></i> Location</th>
                                            <th><i class="fas fa-calendar-plus me-1"></i> Added Date</th>
                                            <th><i class="fas fa-cog me-1"></i> Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($hotel = $recent_hotels->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($hotel['hotel_name']); ?></td>
                                                <td><?php echo htmlspecialchars($hotel['location']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($hotel['created_at'])); ?></td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="add_hotels.php?edit=<?php echo $hotel['id']; ?>"
                                                            class="btn btn-sm btn-primary" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="delete_hotel.php?id=<?php echo $hotel['id']; ?>"
                                                            class="btn btn-sm btn-danger" title="Delete"
                                                            onclick="return confirm('Are you sure you want to delete this hotel?')">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </a>
                                                        <a href="hotel_details.php?id=<?php echo $hotel['id']; ?>"
                                                            class="btn btn-sm btn-info" title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row mt-4">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-chart-line me-2"></i> Monthly Revenue</h3>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="revenueChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-chart-pie me-2"></i> Hotel Distribution</h3>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="locationChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Revenue (in ₹)',
                data: [12000, 19000, 15000, 22000, 25000, 30000, 35000, 40000, 32000, 38000, 42000, 50000],
                backgroundColor: 'rgba(67, 97, 238, 0.2)',
                borderColor: 'rgba(67, 97, 238, 1)',
                borderWidth: 2,
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            }
        }
    });

    // Location Distribution Chart
    const locationCtx = document.getElementById('locationChart').getContext('2d');
    const locationChart = new Chart(locationCtx, {
        type: 'doughnut',
        data: {
            labels: ['Delhi', 'Mumbai', 'Bangalore', 'Hyderabad', 'Chennai'],
            datasets: [{
                data: [12, 19, 8, 5, 10],
                backgroundColor: [
                    '#4361ee',
                    '#4cc9f0',
                    '#f8961e',
                    '#f72585',
                    '#3f37c9'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                }
            }
        }
    });
</script>

<?php include 'layouts/footer.php'; ?>