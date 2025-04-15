<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";  // Default XAMPP password is empty
$dbname = "movie_booking";

try {
    // Create connection
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $movie_id = $_GET['movie_id'] ?? null;
    $date = $_GET['date'] ?? null;
    $theatre_id = $_GET['theatre_id'] ?? null;
    
    if (!$movie_id || !$date) {
        throw new Exception('Movie ID and date are required');
    }
    
    $sql = "SELECT s.*, m.title as movie_title, t.name as theatre_name, t.location as theatre_location 
            FROM shows s 
            JOIN movies m ON s.movie_id = m.id 
            JOIN theatres t ON s.theatre_id = t.id 
            WHERE s.movie_id = :movie_id AND DATE(s.show_date) = :date";
    
    $params = [':movie_id' => $movie_id, ':date' => $date];
    
    if ($theatre_id) {
        $sql .= " AND s.theatre_id = :theatre_id";
        $params[':theatre_id'] = $theatre_id;
    }
    
    $sql .= " ORDER BY s.show_time";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $shows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($shows)) {
        die(json_encode([
            'success' => false,
            'message' => 'No shows available for the selected date and theatre',
            'shows' => []
        ]));
    } else {
        die(json_encode([
            'success' => true,
            'message' => 'Shows found successfully',
            'shows' => $shows
        ]));
    }
} catch (Exception $e) {
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'message' => 'Error fetching shows: ' . $e->getMessage(),
        'shows' => []
    ]));
}

$conn->close();
?> 