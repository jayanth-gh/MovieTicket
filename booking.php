<?php
// Enable error reporting but don't display errors
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set headers for CORS and JSON
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
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

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    // Validate required fields
    if (!isset($input['show_id']) || !isset($input['seats']) || !isset($input['customer_name']) || !isset($input['email'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing required fields',
            'debug' => [
                'input' => $input
            ]
        ]);
        exit();
    }

    // Start transaction
    $conn->beginTransaction();

    // Check if show exists and has enough seats
    $stmt = $conn->prepare("
        SELECT s.*, m.title as movie_title, m.language
        FROM shows s
        JOIN movies m ON s.movie_id = m.id
        WHERE s.id = ?
    ");
    $stmt->execute([$input['show_id']]);
    $show = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$show) {
        $conn->rollBack();
        echo json_encode([
            'success' => false,
            'message' => 'Show not found',
            'debug' => [
                'show_id' => $input['show_id']
            ]
        ]);
        exit();
    }

    $availableSeats = $show['total_seats'] - $show['booked_seats'];
    if ($availableSeats < $input['seats']) {
        $conn->rollBack();
        echo json_encode([
            'success' => false,
            'message' => 'Not enough seats available',
            'debug' => [
                'available' => $availableSeats,
                'requested' => $input['seats']
            ]
        ]);
        exit();
    }

    // Create booking
    $stmt = $conn->prepare("
        INSERT INTO bookings (show_id, customer_name, customer_email, seats, booking_date)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute([
        $input['show_id'],
        $input['customer_name'],
        $input['email'],
        $input['seats']
    ]);
    $bookingId = $conn->lastInsertId();

    // Update booked seats
    $stmt = $conn->prepare("
        UPDATE shows
        SET booked_seats = booked_seats + ?
        WHERE id = ?
    ");
    $stmt->execute([$input['seats'], $input['show_id']]);

    // Commit transaction
    $conn->commit();

    // Return success response with booking details
    echo json_encode([
        'success' => true,
        'message' => 'Booking created successfully',
        'booking_id' => $bookingId,
        'booking' => [
            'id' => $bookingId,
            'movie_title' => $show['movie_title'],
            'language' => $show['language'],
            'show_time' => $show['show_time'],
            'seats' => $input['seats'],
            'customer_name' => $input['customer_name'],
            'email' => $input['email']
        ]
    ]);

} catch (PDOException $e) {
    if (isset($conn)) {
        $conn->rollBack();
    }
    error_log('Database error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'debug' => [
            'error' => $e->getMessage(),
            'code' => $e->getCode()
        ]
    ]);
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollBack();
    }
    error_log('General error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'debug' => [
            'error' => $e->getMessage(),
            'type' => get_class($e)
        ]
    ]);
}
?> 