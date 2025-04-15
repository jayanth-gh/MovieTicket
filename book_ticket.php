<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($input['show_id']) || !isset($input['seats']) || !isset($input['customer_name']) || !isset($input['email'])) {
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";  // Default XAMPP password is empty
$dbname = "movie_booking";

try {
    // Create connection
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Start transaction
    $conn->beginTransaction();
    
    try {
        // Check show availability
        $stmt = $conn->prepare("
            SELECT s.*, m.title as movie_title 
            FROM shows s 
            JOIN movies m ON s.movie_id = m.id 
            WHERE s.id = ? AND s.total_seats >= s.booked_seats + ?
        ");
        $stmt->execute([$input['show_id'], $input['seats']]);
        $show = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$show) {
            throw new Exception('Show not available or not enough seats');
        }
        
        // Create booking
        $stmt = $conn->prepare("
            INSERT INTO bookings (show_id, customer_name, email, seats) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $input['show_id'],
            $input['customer_name'],
            $input['email'],
            $input['seats']
        ]);
        $booking_id = $conn->lastInsertId();
        
        // Update booked seats
        $stmt = $conn->prepare("
            UPDATE shows 
            SET booked_seats = booked_seats + ? 
            WHERE id = ?
        ");
        $stmt->execute([$input['seats'], $input['show_id']]);
        
        // Commit transaction
        $conn->commit();
        
        // Prepare success response with redirect URL
        $redirect_url = "booking_confirmation.html?" . http_build_query([
            'booking_id' => $booking_id,
            'movie_title' => $show['movie_title'],
            'show_time' => $show['show_time'],
            'show_date' => $show['show_date'],
            'seats' => $input['seats'],
            'customer_name' => $input['customer_name'],
            'customer_email' => $input['email']
        ]);
        
        echo json_encode([
            'success' => true,
            'booking_id' => $booking_id,
            'redirect_url' => $redirect_url
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'debug' => [
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}
?> 