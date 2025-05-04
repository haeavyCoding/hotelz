<?php
session_start();


if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: hotels.php');
    exit();
}
require_once 'config.php';
$hotel_id = $_GET['id'];

$hotel_query = "SELECT email FROM hotels WHERE id = ?";
$stmt = $conn->prepare($hotel_query);
$stmt->bind_param("i", $hotel_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $error = "Hotel not found.";
} else {
    $hotel = $result->fetch_assoc();
    $email = $hotel['email'];
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';

    if (empty($password)) {
        $error = "⚠️ Password cannot be empty.";
    } else {
        $check_query = "SELECT id FROM clients WHERE clients_email = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $error = "⚠️ Client with this email already exists.";
        } else {
            // Store password in plain text (not recommended for production)
            $insert_query = "INSERT INTO clients (clients_email, password, hotel_id) VALUES (?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("ssi", $email, $password, $hotel_id);

            if ($insert_stmt->execute()) {
                header("Location: hotel_details.php");
            } else {
                $error = "❌ Database Error: " . $insert_stmt->error;
            }
        }
    }
}
include_once("layouts/header.php");
?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Client</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --success: #4cc9f0;
            --danger: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --border: #dee2e6;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fb;
            color: var(--dark);
            line-height: 1.6;
        }
        
        .container {
            width: 100%;
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        
        .header {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        .header h2 {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }
        
        .header p {
            color: var(--gray);
            font-size: 0.875rem;
        }
        
        .email-display {
            background-color: var(--light);
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
            font-size: 0.9rem;
        }
        
        .email-display strong {
            color: var(--primary);
            font-weight: 500;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            font-size: 0.875rem;
            color: var(--dark);
        }
        
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.9375rem;
            transition: border-color 0.3s;
        }
        
        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
        }
        
        .btn {
            width: 100%;
            padding: 0.875rem;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: var(--primary-dark);
        }
        
        .alert {
            padding: 0.875rem 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }
        
        .alert-success {
            background-color: rgba(76, 201, 240, 0.15);
            color: #0c8599;
            border-left: 4px solid var(--success);
        }
        
        .alert-danger {
            background-color: rgba(247, 37, 133, 0.1);
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }
        
        .password-strength {
            margin-top: 0.5rem;
            font-size: 0.75rem;
            color: var(--gray);
        }
        
        @media (max-width: 576px) {
            .container {
                /* margin: 1rem; */
                padding: 1.5rem;
            }
        }
    </style>
<main class="app-main px-2">

<div class="container">
    <div class="header">
        <h2>Create Client Account</h2>
        <p>Set up a new client for your hotel</p>
    </div>

    <?php if (!empty($email)): ?>
        <div class="email-display">
            Hotel Email: <strong><?php echo htmlspecialchars($email); ?></strong>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (empty($success)): ?>
    <form method="POST">
        <div class="form-group">
            <label for="password">Password</label>
            <input type="text" name="password" id="password" required placeholder="Enter client password">
            <div class="password-strength">Password will be stored as plain text</div>
        </div>
        <button type="submit" class="btn">Create Client Account</button>
    </form>
    <?php endif; ?>
</div>
</main>
<?php include_once('layouts/footer.php'); ?>