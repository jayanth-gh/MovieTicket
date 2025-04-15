<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set proper content type
header('Content-Type: text/html; charset=UTF-8');

// Check if PHP is working
if (!function_exists('phpinfo')) {
    die('PHP is not properly installed or configured');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - MovieHub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #111111;
            color: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.05);
            padding: 2rem;
            border-radius: 12px;
            width: 100%;
            max-width: 400px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            background: linear-gradient(45deg, #ff4d4d, #f9cb28);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .form-control {
            width: 100%;
            padding: 0.8rem 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: #fff;
            font-size: 1rem;
        }

        .form-control:focus {
            outline: none;
            border-color: rgba(255, 255, 255, 0.3);
        }

        .login-btn {
            width: 100%;
            padding: 0.8rem;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .login-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .error-message {
            color: #ff4d4d;
            text-align: center;
            margin-top: 1rem;
            display: none;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Admin Login</h1>
            <p>Enter your credentials to access the admin panel</p>
        </div>
        <form action="admin_login.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
            <?php
            if (isset($_GET['error'])) {
                echo '<div class="error-message" style="display: block;">' . htmlspecialchars($_GET['error']) . '</div>';
            }
            ?>
        </form>
    </div>
</body>
</html> 