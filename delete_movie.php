<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['movie_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing movie ID']);
    exit;
}

require_once 'config.php';

try {
    // Start transaction
    $pdo->beginTransaction();

    // First delete all shows for this movie
    $stmt = $pdo->prepare("DELETE FROM shows WHERE movie_id = ?");
    $stmt->execute([$input['movie_id']]);

    // Then delete the movie
    $stmt = $pdo->prepare("DELETE FROM movies WHERE id = ?");
    $stmt->execute([$input['movie_id']]);

    // Commit transaction
    $pdo->commit();
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} 