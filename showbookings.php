<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
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

    // Get parameters
    $movie_id = isset($_GET['movie_id']) ? $_GET['movie_id'] : null;
    $date = isset($_GET['date']) ? $_GET['date'] : null;

    if (!$movie_id || !$date) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing required parameters',
            'debug' => [
                'movie_id' => $movie_id,
                'date' => $date
            ]
        ]);
        exit();
    }

    // Fetch shows for the movie on the specified date
    $stmt = $conn->prepare("
        SELECT s.id, s.show_time, s.total_seats, s.booked_seats,
               m.title, m.language
        FROM shows s
        JOIN movies m ON s.movie_id = m.id
        WHERE s.movie_id = ? AND s.show_date = ?
        ORDER BY s.show_time
    ");
    $stmt->execute([$movie_id, $date]);
    $shows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($shows)) {
        echo json_encode([
            'success' => true,
            'message' => 'No shows available for this date',
            'shows' => []
        ]);
        exit();
    }

    echo json_encode([
        'success' => true,
        'shows' => $shows
    ]);

} catch (PDOException $e) {
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