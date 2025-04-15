<?php
session_start();
header('Content-Type: application/json');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON input']);
    exit;
}

if (!isset($input['movie_id']) || !isset($input['show_date']) || 
    !isset($input['show_time']) || !isset($input['total_seats'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

require_once 'config.php';

try {
    // Verify movie exists
    $stmt = $pdo->prepare("SELECT id FROM movies WHERE id = ?");
    $stmt->execute([$input['movie_id']]);
    if (!$stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid movie ID']);
        exit;
    }

    // Insert new show
    $stmt = $pdo->prepare("
        INSERT INTO shows (movie_id, show_date, show_time, total_seats, booked_seats) 
        VALUES (?, ?, ?, ?, 0)
    ");
    $result = $stmt->execute([
        $input['movie_id'],
        $input['show_date'],
        $input['show_time'],
        $input['total_seats']
    ]);
    
    if ($result) {
        echo json_encode([
            'success' => true, 
            'show_id' => $pdo->lastInsertId(),
            'message' => 'Show added successfully'
        ]);
    } else {
        throw new Exception('Failed to add show');
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error',
        'message' => $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Operation failed',
        'message' => $e->getMessage()
    ]);
}
?> 