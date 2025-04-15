<?php
// Add CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
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

    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);

    // Validate required fields
    if (!isset($data['title']) || !isset($data['genre']) || !isset($data['duration']) || 
        !isset($data['language']) || !isset($data['release_date'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing required fields'
        ]);
        exit;
    }

    // Prepare SQL statement
    $stmt = $conn->prepare("
        INSERT INTO movies (title, genre, duration, language, release_date, poster_url)
        VALUES (:title, :genre, :duration, :language, :release_date, :poster_url)
    ");

    // Execute the statement
    $stmt->execute([
        'title' => $data['title'],
        'genre' => $data['genre'],
        'duration' => $data['duration'],
        'language' => $data['language'],
        'release_date' => $data['release_date'],
        'poster_url' => $data['poster'] ?? null
    ]);

    // Get the ID of the newly inserted movie
    $movieId = $conn->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Movie added successfully',
        'movie_id' => $movieId
    ]);

} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 