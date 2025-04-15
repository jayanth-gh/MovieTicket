<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit();
}

// Get the raw POST data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON data received'
    ]);
    exit();
}

// Validate required fields
$required_fields = ['movie_id', 'show_id', 'seats', 'customer_name', 'customer_email'];
foreach ($required_fields as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => "Missing required field: $field"
        ]);
        exit();
    }
}

// Database connection details
$host = 'localhost';
$dbname = 'movie_booking';
$username = 'root';
$password = '';

try {
    // Create database connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Get show details including theatre information
    $stmt = $pdo->prepare("
        SELECT s.*, m.title as movie_title, t.name as theatre_name, t.location as theatre_location 
        FROM shows s 
        JOIN movies m ON s.movie_id = m.id 
        JOIN theatres t ON s.theatre_id = t.id 
        WHERE s.id = ?
    ");
    $stmt->execute([$data['show_id']]);
    $show = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$show) {
        throw new Exception('Show not found');
    }
    
    // Check if enough seats are available
    if ($show['booked_seats'] + $data['seats'] > $show['total_seats']) {
        throw new Exception('Not enough seats available');
    }
    
    // Insert booking
    $stmt = $pdo->prepare("INSERT INTO bookings (show_id, seats, customer_name, customer_email, booking_date) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([
        $data['show_id'],
        $data['seats'],
        $data['customer_name'],
        $data['customer_email']
    ]);
    
    $booking_id = $pdo->lastInsertId();
    
    // Update show's booked seats
    $stmt = $pdo->prepare("UPDATE shows SET booked_seats = booked_seats + ? WHERE id = ?");
    $stmt->execute([$data['seats'], $data['show_id']]);
    
    // Commit transaction
    $pdo->commit();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Booking created successfully',
        'booking_id' => $booking_id,
        'movie_title' => $show['movie_title'],
        'theatre_name' => $show['theatre_name'],
        'theatre_location' => $show['theatre_location'],
        'show_time' => $show['show_time'],
        'seats' => $data['seats'],
        'customer_name' => $data['customer_name']
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 