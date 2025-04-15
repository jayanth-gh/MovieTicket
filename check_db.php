<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for JSON response
header('Content-Type: application/json');

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";  // Default XAMPP password is empty
$dbname = "movie_booking";

try {
    // Create connection
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get all movies
    $stmt = $conn->query("SELECT * FROM movies");
    $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get all shows
    $stmt = $conn->query("SELECT * FROM shows");
    $shows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format the response
    $response = [
        'success' => true,
        'data' => [
            'movies' => $movies,
            'shows' => $shows,
            'movie_count' => count($movies),
            'show_count' => count($shows)
        ]
    ];

    echo json_encode($response);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?> 