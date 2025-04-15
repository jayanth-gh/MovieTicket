<?php
// Database connection details
$servername = "localhost";
$username = "root";
$password = "";  // Default XAMPP password is empty
$dbname = "movie_booking";

try {
    // Create connection
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Start transaction
    $conn->beginTransaction();

    // First, clear existing data
    $conn->exec("DELETE FROM bookings");
    $conn->exec("DELETE FROM shows");
    $conn->exec("DELETE FROM movies");
    
    // Insert new movies
    $movies = [
        ['Inception', 'Sci-Fi', 148, 'English', '2024-04-10'],
        ['The Dark Knight', 'Action', 152, 'English', '2024-04-11'],
        ['Interstellar', 'Sci-Fi', 169, 'English', '2024-04-12']
    ];
    
    $stmt = $conn->prepare("INSERT INTO movies (title, genre, duration, language, release_date) VALUES (?, ?, ?, ?, ?)");
    foreach ($movies as $movie) {
        $stmt->execute($movie);
    }
    
    // Get the movie IDs
    $movieIds = $conn->query("SELECT id FROM movies")->fetchAll(PDO::FETCH_COLUMN);
    
    // Insert new shows with future dates
    $shows = [];
    $currentDate = date('Y-m-d');
    $futureDate = date('Y-m-d', strtotime('+1 day')); // Tomorrow's date
    
    foreach ($movieIds as $movieId) {
        // Add multiple shows for each movie
        $shows[] = [$movieId, $futureDate, '14:00:00', 100];
        $shows[] = [$movieId, $futureDate, '18:00:00', 100];
        $shows[] = [$movieId, date('Y-m-d', strtotime('+2 days')), '15:00:00', 100];
        $shows[] = [$movieId, date('Y-m-d', strtotime('+2 days')), '19:00:00', 100];
    }
    
    $stmt = $conn->prepare("INSERT INTO shows (movie_id, show_date, show_time, total_seats) VALUES (?, ?, ?, ?)");
    foreach ($shows as $show) {
        $stmt->execute($show);
    }
    
    // Commit transaction
    $conn->commit();
    
    echo "Successfully inserted new movies and shows!<br>";
    echo "Number of movies inserted: " . count($movies) . "<br>";
    echo "Number of shows inserted: " . count($shows) . "<br>";
    echo "Current date: " . $currentDate . "<br>";
    echo "Future shows date: " . $futureDate . "<br>";
    
} catch(PDOException $e) {
    // Rollback transaction on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "Error: " . $e->getMessage();
}
?> 