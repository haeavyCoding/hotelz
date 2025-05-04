<?php
session_start();
// Redirect to login if not authenticated
if (!isset($_SESSION['user'])) {
    header('Location: auth_system/login.php');
    exit();
}

require_once 'config.php';

// Set default timezone
date_default_timezone_set('UTC');

// CSRF token generation and validation
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle delete action
if (isset($_GET['delete_id']) && isset($_GET['csrf_token'])) {
    try {
        // Validate CSRF token
        if (!hash_equals($_SESSION['csrf_token'], $_GET['csrf_token'])) {
            throw new Exception("CSRF token validation failed");
        }

        $delete_id = (int)$_GET['delete_id'];

        // Verify client exists first
        $check_query = "SELECT id FROM clients WHERE id = ?";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, 'i', $delete_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result)) {
            $delete_query = "DELETE FROM clients WHERE id = ?";
            $stmt = mysqli_prepare($conn, $delete_query);
            mysqli_stmt_bind_param($stmt, 'i', $delete_id);

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['message'] = "Client deleted successfully";
                $_SESSION['message_type'] = "success";
            } else {
                throw new Exception("Error deleting client");
            }
        } else {
            throw new Exception("Client not found");
        }
    } catch (Exception $e) {
        $_SESSION['message'] = $e->getMessage();
        $_SESSION['message_type'] = "error";
    } finally {
        header("Location: clints_id_pass.php");
        exit();
    }
}

// Handle edit action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_client'])) {
    try {
        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            throw new Exception("CSRF token validation failed");
        }

        $client_id = (int)$_POST['client_id'];
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        $hotel_id = !empty($_POST['hotel_id']) ? (int)$_POST['hotel_id'] : null;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        $update_query = "UPDATE clients SET
                        clients_email = ?,
                        password = ?,
                        hotel_id = ?
                        WHERE id = ?";

        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, 'ssii', $email, $password, $hotel_id, $client_id);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['message'] = "Client updated successfully";
            $_SESSION['message_type'] = "success";
        } else {
            throw new Exception("Error updating client: " . mysqli_error($conn));
        }
    } catch (Exception $e) {
        $_SESSION['message'] = $e->getMessage();
        $_SESSION['message_type'] = "error";
    } finally {
        header("Location: clints_id_pass.php");
        exit();
    }
}

// Fetch clients data with prepared statement
$query = "SELECT clients.id, clients.clients_email, clients.hotel_id,
          clients.created_at, hotels.hotel_name, clients.password
          FROM clients
          LEFT JOIN hotels ON clients.hotel_id = hotels.id
          ORDER BY clients.id DESC";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Database error: " . mysqli_error($conn));
}

// Fetch hotels for dropdown
$hotels = [];
$hotels_query = "SELECT id, hotel_name FROM hotels";
$hotels_result = mysqli_query($conn, $hotels_query);

if ($hotels_result) {
    while ($row = mysqli_fetch_assoc($hotels_result)) {
        $hotels[$row['id']] = htmlspecialchars($row['hotel_name']);
    }
}
include_once('layouts/header.php');
?>
    <title>Clients Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-light: #eef2ff;
            --secondary-color: #3f37c9;
            --success-color: #4cc9f0;
            --danger-color: #f72585;
            --warning-color: #f8961e;
            --info-color: #4895ef;
            --dark-color: #212529;
            --light-color: #f8f9fa;
            --gray-color: #6c757d;
            --border-color: #dee2e6;
            --border-radius: 0.375rem;
            --box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fb;
            color: var(--dark-color);
            line-height: 1.6;
            min-height: 100vh;
        }

        .container {
            width: 100%;
            max-width: 1400px;
            padding: 1rem;
            margin: 0 auto;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            text-align: center;
            padding: 1rem 0;
        }

        .card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 2rem;
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .card-header {
            padding: 1.25rem 1.5rem;
            background-color: var(--primary-light);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
            margin: 0;
        }

        .card-body {
            padding: 1.5rem;
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }

        .table th, .table td {
            padding: 1rem;
            vertical-align: middle;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .table thead th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            position: sticky;
            top: 0;
        }

        .table tbody tr:hover {
            background-color: rgba(67, 97, 238, 0.05);
        }

        .badge {
            display: inline-block;
            padding: 0.35em 0.65em;
            font-size: 0.75em;
            font-weight: 600;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 50rem;
        }

        .badge-primary {
            background-color: var(--primary-light);
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            font-size: 0.875rem;
            font-weight: 500;
            line-height: 1.5;
            text-align: center;
            text-decoration: none;
            vertical-align: middle;
            cursor: pointer;
            user-select: none;
            border: 1px solid transparent;
            transition: var(--transition);
            gap: 0.5rem;
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.8125rem;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .btn-danger {
            background-color: var(--danger-color);
            color: white;
            border-color: var(--danger-color);
        }

        .btn-danger:hover {
            background-color: #d1145a;
            border-color: #d1145a;
        }

        .btn-info {
            background-color: var(--info-color);
            color: white;
            border-color: var(--info-color);
        }

        .btn-info:hover {
            background-color: #3a7bc8;
            border-color: #3a7bc8;
        }

        .btn-group {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .text-muted {
            color: var(--gray-color);
            font-style: italic;
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 28px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .toggle-slider {
            background-color: var(--primary-color);
        }

        input:checked + .toggle-slider:before {
            transform: translateX(32px);
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark-color);
        }

        .form-control {
            display: block;
            width: 100%;
            padding: 0.5rem 0.75rem;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: var(--dark-color);
            background-color: white;
            background-clip: padding-box;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            outline: 0;
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
        }

        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1050;
            opacity: 0;
            visibility: hidden;
            transition: var(--transition);
        }

        .modal.show {
            opacity: 1;
            visibility: visible;
        }

        .modal-dialog {
            width: 100%;
            max-width: 600px;
            margin: 1.75rem;
        }

        .modal-content {
            position: relative;
            display: flex;
            flex-direction: column;
            width: 100%;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            outline: 0;
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .modal-title {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .modal-body {
            position: relative;
            flex: 1 1 auto;
            padding: 1.5rem;
            max-height: 70vh;
            overflow-y: auto;
        }

        .modal-footer {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 1.25rem 1.5rem;
            border-top: 1px solid var(--border-color);
            gap: 0.75rem;
        }

        .close {
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1;
            color: #000;
            opacity: 0.5;
            background: none;
            border: none;
            cursor: pointer;
            transition: var(--transition);
            padding: 0.5rem;
        }

        .close:hover {
            opacity: 1;
            transform: scale(1.1);
        }

        .alert {
            position: relative;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid transparent;
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .alert-success {
            color: #0f5132;
            background-color: #d1e7dd;
            border-color: #badbcc;
        }

        .alert-danger {
            color: #842029;
            background-color: #f8d7da;
            border-color: #f5c2c7;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--gray-color);
        }

        .empty-state-icon {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            color: var(--border-color);
        }

        .password-field {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .password-text {
            font-family: monospace;
            letter-spacing: 1px;
            font-size: 0.95em;
        }

        .copy-btn {
            background: none;
            border: none;
            color: var(--gray-color);
            cursor: pointer;
            transition: var(--transition);
            padding: 0.25rem;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .copy-btn:hover {
            color: var(--primary-color);
            background-color: rgba(67, 97, 238, 0.1);
        }

        .tooltip {
            position: relative;
            display: inline-block;
        }

        .tooltip .tooltip-text {
            visibility: hidden;
            width: 120px;
            background-color: var(--dark-color);
            color: white;
            text-align: center;
            border-radius: var(--border-radius);
            padding: 0.5rem;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
            transition: opacity 0.3s;
            font-size: 0.75rem;
        }

        .tooltip:hover .tooltip-text {
            visibility: visible;
            opacity: 1;
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px 12px;
            padding-right: 2.5rem;
        }

        @media (max-width: 991.98px) {
            .container {
                padding: 0.75rem;
            }

            .page-title {
                font-size: 1.75rem;
            }

            .table th, .table td {
                padding: 0.75rem;
            }
        }

        @media (max-width: 767.98px) {
            .table-responsive {
                display: block;
                width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
                padding: 1rem;
            }

            .card-title {
                font-size: 1.25rem;
            }

            .btn-group {
                width: 100%;
                justify-content: flex-start;
            }

            .modal-dialog {
                margin: 0.5rem;
            }
        }

        @media (max-width: 575.98px) {
            .page-title {
                font-size: 1.5rem;
                padding: 0.5rem 0;
            }

            .card-body {
                padding: 1rem;
            }

            .modal-body {
                padding: 1rem;
            }

            .modal-footer {
                padding: 1rem;
            }

            .empty-state {
                padding: 2rem 1rem;
            }
        }
    </style>
    <main class="app-main">
        <div class="container">
            <h1 class="page-title">Clients Management</h1>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['message_type'] == 'success' ? 'success' : 'danger'; ?>">
                    <i class="fas fa-<?php echo $_SESSION['message_type'] == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <span><?php echo $_SESSION['message']; ?></span>
                </div>
                <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Clients List</h2>
                    <div class="form-group" style="margin: 0;">
                        <label for="togglePassword" style="margin-right: 0.5rem; font-size: 0.875rem; font-weight: 500;">Show Passwords</label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="togglePassword">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Email</th>
                                    <th>Password</th>
                                    <th>Hotel</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($result) > 0): ?>
                                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                                        <tr>
                                            <td><?php echo $row['id']; ?></td>
                                            <td><?php echo htmlspecialchars($row['clients_email'], ENT_QUOTES); ?></td>
                                            <td>
                                                <div class="password-field">
                                                    <span class="password-text hidden-password">••••••••••</span>
                                                    <span class="password-text visible-password" style="display: none;"><?php echo htmlspecialchars($row['password'], ENT_QUOTES); ?></span>
                                                    <span class="tooltip">
                                                        <button class="copy-btn" onclick="copyToClipboard(this, '<?php echo htmlspecialchars($row['password'], ENT_QUOTES); ?>')">
                                                            <i class="far fa-copy"></i>
                                                        </button>
                                                        <span class="tooltip-text">Copy password</span>
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($row['hotel_name']): ?>
                                                    <span class="badge badge-primary"><?php echo htmlspecialchars($row['hotel_name'], ENT_QUOTES); ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('M d, Y H:i', strtotime($row['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <button class="btn btn-primary btn-sm" onclick="openEditModal(
                                                        <?php echo $row['id']; ?>,
                                                        '<?php echo htmlspecialchars($row['clients_email'], ENT_QUOTES); ?>',
                                                        '<?php echo htmlspecialchars($row['password'], ENT_QUOTES); ?>',
                                                        '<?php echo $row['hotel_id']; ?>'
                                                    )">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                    <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?php echo $row['id']; ?>)">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6">
                                            <div class="empty-state">
                                                <div class="empty-state-icon">
                                                    <i class="far fa-folder-open"></i>
                                                </div>
                                                <h3>No clients found</h3>
                                                <p class="text-muted">There are currently no clients in the system</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Client Modal -->
        <div id="editModal" class="modal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Client</h5>
                        <button type="button" class="close" onclick="closeModal('editModal')">&times;</button>
                    </div>
                    <form method="POST" action="">
                        <div class="modal-body">
                            <input type="hidden" name="edit_client" value="1">
                            <input type="hidden" name="client_id" id="edit_client_id">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                            <div class="form-group">
                                <label for="edit_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="edit_email" name="email" required>
                            </div>

                            <div class="form-group">
                                <label for="edit_password" class="form-label">Password</label>
                                <input type="text" class="form-control" id="edit_password" name="password" required>
                            </div>

                            <div class="form-group">
                                <label for="edit_hotel_id" class="form-label">Hotel</label>
                                <select class="form-control" id="edit_hotel_id" name="hotel_id">
                                    <option value="">Select Hotel</option>
                                    <?php foreach ($hotels as $id => $name): ?>
                                        <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>


    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('change', function() {
            const isChecked = this.checked;
            document.querySelectorAll('.hidden-password').forEach(el => {
                el.style.display = isChecked ? 'none' : 'inline';
            });
            document.querySelectorAll('.visible-password').forEach(el => {
                el.style.display = isChecked ? 'inline' : 'none';
            });
        });

        // Copy password to clipboard
        function copyToClipboard(button, text) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            document.body.appendChild(textarea);
            textarea.select();

            try {
                document.execCommand('copy');
                const icon = button.querySelector('i');
                const originalClass = icon.className;
                icon.className = 'fas fa-check';

                const tooltip = button.parentElement.querySelector('.tooltip-text');
                if (tooltip) {
                    tooltip.textContent = 'Copied!';
                    setTimeout(() => {
                        tooltip.textContent = 'Copy password';
                        icon.className = originalClass;
                    }, 2000);
                }
            } catch (err) {
                console.error('Failed to copy:', err);
                Swal.fire({
                    icon: 'error',
                    title: 'Failed to copy',
                    text: 'Please try again or copy manually'
                });
            } finally {
                document.body.removeChild(textarea);
            }
        }

        // Modal functions
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
            document.body.style.overflow = 'auto';
        }

        // Edit client modal
        function openEditModal(id, email, password, hotelId) {
            document.getElementById('edit_client_id').value = id;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_password').value = password;
            document.getElementById('edit_hotel_id').value = hotelId || '';
            openModal('editModal');
        }

        // Confirm delete with SweetAlert
        function confirmDelete(clientId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Include CSRF token in the delete request
                    const csrfToken = '<?php echo $_SESSION['csrf_token']; ?>';
                    window.location.href = 'clints_id_pass.php?delete_id=' + clientId + '&csrf_token=' + encodeURIComponent(csrfToken);
                }
            });
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('show');
                document.body.style.overflow = 'auto';
            }
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                document.querySelectorAll('.modal.show').forEach(modal => {
                    modal.classList.remove('show');
                    document.body.style.overflow = 'auto';
                });
            }
        });
    </script>
    <?php include_once('layouts/footer.php'); ?>
