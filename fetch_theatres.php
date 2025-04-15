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
    
    if ($movie_id) {
        // Get theatres showing the specific movie
        $sql = "SELECT DISTINCT t.id, t.name, t.location 
                FROM theatres t 
                JOIN shows s ON t.id = s.theatre_id 
                WHERE s.movie_id = :movie_id 
                ORDER BY t.name";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':movie_id', $movie_id, PDO::PARAM_INT);
        $stmt->execute();
        $theatres = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Get all theatres
        $sql = "SELECT id, name, location FROM theatres ORDER BY name";
        $stmt = $conn->query($sql);
        $theatres = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    if (empty($theatres)) {
        echo json_encode([
            'success' => true, 
            'theatres' => [], 
            'message' => 'No theatres available for this movie'
        ]);
    } else {
        echo json_encode(['success' => true, 'theatres' => $theatres]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?> 