<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}

// Demo credentials (use DB in production)
$valid_username = 'admin';
$valid_password_hash = password_hash('password123', PASSWORD_DEFAULT);

// Error messages for customization
$invalid_credentials_message = "Oops! Invalid username or password. Try again 😊";
$login_success_message = "Login successful! Welcome 👋";

// Motivational Quotes Array
$quotes = [
    "Believe you can and you're halfway there. ✨",
    "Don't watch the clock; do what it does. Keep going. ⏰",
    "The harder you work for something, the greater you'll feel when you achieve it. 💪",
    "Success is the sum of small efforts, repeated day in and day out. 🌟",
    "Opportunities don't happen, you create them. 💡",
    "The future belongs to those who believe in the beauty of their dreams. 🌙"
];

// Select a random quote
$random_quote = $quotes[array_rand($quotes)];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $remember_me = isset($_POST['remember_me']);

    if ($username === $valid_username && password_verify($password, $valid_password_hash)) {
        $_SESSION['user'] = $username;

        if ($remember_me) {
            setcookie('user', $username, time() + 3600 * 24 * 30); // Remember for 30 days
        }

        $success_message = $login_success_message;
        header('Location: ../index.php');
        exit();
    } else {
        $error = $invalid_credentials_message;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Motivational Login Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
         <style>
        :root {
            --primary: #4a6bff;
            --secondary: #6c5ce7;
            --error: #ff4757;
            --gray: #dfe4ea;
            --light: #f1f2f6;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 420px;
        }

        .logo {
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin: auto;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 28px;
            box-shadow: 0 8px 20px rgba(74, 107, 255, 0.3);
        }

        .title {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            margin: 20px 0;
            color:rgb(71, 79, 97);
        }

        .subtitle {
            text-align: center;
            color: #777;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .input-group {
            position: relative;
            margin-bottom: 20px;
        }

        .input-group input {
            width: 100%;
            padding: 12px 40px 12px 40px;
            border: 2px solid var(--gray);
            border-radius: 10px;
            background-color: var(--light);
            font-size: 15px;
        }

        .input-group i {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        .input-group .fa-user,
        .input-group .fa-key {
            left: 12px;
        }

        .input-group .password-toggle {
            right: 12px;
            cursor: pointer;
        }

        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: 0.3s ease;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .error-message {
            background: rgba(255, 71, 87, 0.1);
            color: var(--error);
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            font-size: 14px;
            gap: 10px;
            border-left: 4px solid var(--error);
        }

        .success-message {
            background: rgba(74, 178, 45, 0.1);
            color: #2dcb5b;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            font-size: 14px;
            gap: 10px;
            border-left: 4px solid #2dcb5b;
        }

        .links {
            margin-top: 20px;
            text-align: center;
            font-size: 14px;
            display: flex;
            justify-content: space-between;
        }

        .links a {
            color: #666;
            text-decoration: none;
        }

        .links a:hover {
            color: var(--primary);
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            color: #aaa;
            font-size: 12px;
        }

        .remember-me {
            display: flex;
            align-items: center;
        }

        .remember-me input {
            margin-right: 10px;
        }

        @media (max-width: 480px) {
            .container {
                padding: 30px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="logo">
        <i class="fas fa-lock"></i>
    </div>

    <!-- Replace the title with the random quote -->
    <h2 class="title"><?php echo $random_quote; ?></h2>

    <!-- <p class="subtitle">Enter your credentials to access your account</p> -->

    <?php if (isset($error)): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php elseif (isset($success_message)): ?>
        <div class="success-message">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="input-group">
            <i class="fas fa-user"></i>
            <input type="text" name="username" placeholder="Username" required autocomplete="off">
        </div>

        <div class="input-group">
            <i class="fas fa-key"></i>
            <input type="password" name="password" id="password" placeholder="Password" required autocomplete="off">
            <i class="fas fa-eye password-toggle" id="togglePassword"></i>
        </div>


        <button type="submit">Sign In</button>
    </form>

    

    <div class="footer">
        &copy; <?php echo date('Y'); ?> Secure Portal. All rights reserved.
    </div>
</div>

<script>
    // Toggle password visibility
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');

    togglePassword.addEventListener('click', () => {
        const type = passwordInput.type === 'password' ? 'text' : 'password';
        passwordInput.type = type;
        togglePassword.classList.toggle('fa-eye');
        togglePassword.classList.toggle('fa-eye-slash');
    });
</script>
</body>
</html>
