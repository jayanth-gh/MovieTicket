<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

include '../connection.php';

try {
    // Start session
    session_start();
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not logged in');
    }
    
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['show_id']) || !isset($data['num_seats'])) {
        throw new Exception('Show ID and number of seats are required');
    }
    
    $userId = $_SESSION['user_id'];
    $showId = $data['show_id'];
    $numSeats = $data['num_seats'];
    
    // Validate number of seats
    if ($numSeats < 1 || $numSeats > 10) {
        throw new Exception('Number of seats must be between 1 and 10');
    }
    
    // Get show details
    $stmt = $conn->prepare("SELECT price, total_seats, booked_seats FROM shows WHERE id = ?");
    $stmt->bind_param("i", $showId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Show not found');
    }
    
    $show = $result->fetch_assoc();
    
    // Check if enough seats are available
    $availableSeats = $show['total_seats'] - $show['booked_seats'];
    if ($numSeats > $availableSeats) {
        throw new Exception('Not enough seats available. Only ' . $availableSeats . ' seats left.');
    }
    
    // Calculate total price
    $totalPrice = $numSeats * $show['price'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Create booking
        $stmt = $conn->prepare("INSERT INTO bookings (user_id, show_id, num_seats, total_price) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiid", $userId, $showId, $numSeats, $totalPrice);
        
        if (!$stmt->execute()) {
            throw new Exception('Error creating booking: ' . $conn->error);
        }
        
        $bookingId = $conn->insert_id;
        
        // Update show's booked seats
        $newBookedSeats = $show['booked_seats'] + $numSeats;
        $stmt = $conn->prepare("UPDATE shows SET booked_seats = ? WHERE id = ?");
        $stmt->bind_param("ii", $newBookedSeats, $showId);
        
        if (!$stmt->execute()) {
            throw new Exception('Error updating show: ' . $conn->error);
        }
        
        // Commit transaction
        $conn->commit();
        
        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Booking created successfully',
            'booking' => [
                'id' => $bookingId,
                'user_id' => $userId,
                'show_id' => $showId,
                'num_seats' => $numSeats,
                'total_price' => $totalPrice
            ]
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?> 