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

if (!$input || !isset($input['show_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing show ID']);
    exit;
}

require_once 'config.php';

try {
    // Start transaction
    $pdo->beginTransaction();

    // First check if there are any bookings for this show
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE show_id = ?");
    $stmt->execute([$input['show_id']]);
    $bookingCount = $stmt->fetchColumn();

    if ($bookingCount > 0) {
        throw new Exception('Cannot delete show with existing bookings');
    }

    // Delete the show
    $stmt = $pdo->prepare("DELETE FROM shows WHERE id = ?");
    $stmt->execute([$input['show_id']]);

    // Commit transaction
    $pdo->commit();
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 