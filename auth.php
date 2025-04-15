<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get request data
$data = json_decode(file_get_contents('php://input'), true);
$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    // Database connection
    $host = 'localhost';
    $dbname = 'movie_booking';
    $username = 'root';
    $password = '';

    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($action === 'login') {
        // Login
        $email = isset($data['email']) ? $data['email'] : '';
        $password = isset($data['password']) ? $data['password'] : '';

        if (empty($email) || empty($password)) {
            echo json_encode([
                'success' => false,
                'message' => 'Email and password are required'
            ]);
            exit;
        }

        // For demo purposes, we'll just check if the user exists
        $stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'user' => $user
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid email or password'
            ]);
        }
    } elseif ($action === 'signup') {
        // Signup
        $name = isset($data['name']) ? $data['name'] : '';
        $email = isset($data['email']) ? $data['email'] : '';

        if (empty($name) || empty($email)) {
            echo json_encode([
                'success' => false,
                'message' => 'Name and email are required'
            ]);
            exit;
        }

        // Check if user already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingUser) {
            echo json_encode([
                'success' => false,
                'message' => 'User with this email already exists'
            ]);
            exit;
        }

        // Create new user
        $stmt = $pdo->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
        $stmt->execute([$name, $email]);
        $userId = $pdo->lastInsertId();

        echo json_encode([
            'success' => true,
            'message' => 'User created successfully',
            'user' => [
                'id' => $userId,
                'name' => $name,
                'email' => $email
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?> 