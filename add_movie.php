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

if (!isset($input['title']) || !isset($input['language'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

require_once 'config.php';

try {
    $stmt = $pdo->prepare("INSERT INTO movies (title, language) VALUES (?, ?)");
    $result = $stmt->execute([$input['title'], $input['language']]);
    
    if ($result) {
        echo json_encode([
            'success' => true, 
            'movie_id' => $pdo->lastInsertId(),
            'message' => 'Movie added successfully'
        ]);
    } else {
        throw new Exception('Failed to add movie');
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