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

    // Get date from query parameter, default to today
    $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

    // First check if there are any movies
    $stmt = $conn->query("SELECT COUNT(*) FROM movies");
    $movieCount = $stmt->fetchColumn();

    if ($movieCount == 0) {
        echo json_encode([
            'success' => false,
            'message' => 'No movies found in the database',
            'debug' => [
                'date' => $date,
                'movie_count' => $movieCount
            ]
        ]);
        exit();
    }

    // Fetch all movies
    $stmt = $conn->prepare("
        SELECT m.id, m.title, m.language,
               COUNT(s.id) as show_count,
               MIN(s.show_date) as next_show_date,
               MIN(s.show_time) as next_show_time
        FROM movies m
        LEFT JOIN shows s ON m.id = s.movie_id
        GROUP BY m.id, m.title, m.language
        ORDER BY m.title
    ");
    $stmt->execute();
    $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($movies)) {
        echo json_encode([
            'success' => false,
            'message' => 'No movies found in the database',
            'debug' => [
                'query' => $stmt->queryString,
                'error' => $conn->errorInfo()
            ]
        ]);
        exit();
    }

    // Format the response
    $response = [
        'success' => true,
        'movies' => $movies,
        'debug' => [
            'count' => count($movies),
            'date' => $date
        ]
    ];

    echo json_encode($response);

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