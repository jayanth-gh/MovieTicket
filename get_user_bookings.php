<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set headers for CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
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

    // Get email from query parameters
    $email = isset($_GET['email']) ? $_GET['email'] : null;

    if (!$email) {
        echo json_encode([
            'success' => false,
            'message' => 'Email parameter is required'
        ]);
        exit();
    }

    // Get user bookings
    $stmt = $conn->prepare("
        SELECT 
            b.id as booking_id, 
            b.booking_date, 
            b.seats as seats_booked, 
            s.show_time, 
            m.title as movie_title,
            m.language,
            m.poster_url
        FROM bookings b
        JOIN shows s ON b.show_id = s.id
        JOIN movies m ON s.movie_id = m.id
        WHERE b.customer_email = ?
        ORDER BY b.booking_date DESC
    ");
    $stmt->execute([$email]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return success response
    echo json_encode([
        'success' => true,
        'bookings' => $bookings
    ]);

} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log('General error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?> 