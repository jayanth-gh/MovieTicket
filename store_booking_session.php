<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get the raw POST data
$json_data = file_get_contents('php://input');

// Try to decode the JSON data
$booking_data = json_decode($json_data, true);

// Check if JSON decoding was successful
if ($booking_data === null) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit();
}

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Store booking information in session
$_SESSION['booking_info'] = [
    'booking_id' => $booking_data['booking_id'] ?? '',
    'movie_title' => $booking_data['movie_title'] ?? '',
    'show_time' => $booking_data['show_time'] ?? '',
    'seats' => $booking_data['seats'] ?? '',
    'customer_name' => $booking_data['customer_name'] ?? ''
];

// Return success response
echo json_encode([
    'success' => true,
    'message' => 'Booking information stored successfully'
]); 