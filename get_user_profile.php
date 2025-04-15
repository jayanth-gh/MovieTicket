<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

include '../connection.php';

try {
    // Start session
    session_start();
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not logged in');
    }
    
    $userId = $_SESSION['user_id'];
    
    // Get user profile
    $stmt = $conn->prepare("SELECT id, name, email, role, created_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('User not found');
    }
    
    $user = $result->fetch_assoc();
    
    // Get user's bookings
    $stmt = $conn->prepare("
        SELECT b.id, b.num_seats, b.total_price, b.booking_date, b.status,
               s.show_date, s.show_time, s.price,
               m.title as movie_title, m.poster_url,
               t.name as theatre_name, t.location
        FROM bookings b
        JOIN shows s ON b.show_id = s.id
        JOIN movies m ON s.movie_id = m.id
        JOIN theatres t ON s.theatre_id = t.id
        WHERE b.user_id = ?
        ORDER BY b.booking_date DESC
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $bookings = [];
    while ($booking = $result->fetch_assoc()) {
        $bookings[] = $booking;
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'user' => $user,
        'bookings' => $bookings
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?> 