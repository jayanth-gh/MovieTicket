<?php
header('Content-Type: application/json');
include '../connection.php';

try {
    $movie_id = $_GET['movie_id'] ?? null;
    $theatre_id = $_GET['theatre_id'] ?? null;
    
    if (!$movie_id || !$theatre_id) {
        throw new Exception('Movie ID and Theatre ID are required');
    }
    
    $sql = "SELECT s.*, m.title as movie_title, t.name as theatre_name 
            FROM shows s 
            JOIN movies m ON s.movie_id = m.id 
            JOIN theatres t ON s.theatre_id = t.id 
            WHERE s.movie_id = ? AND s.theatre_id = ? 
            AND s.show_date >= CURDATE() 
            ORDER BY s.show_date, s.show_time";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $movie_id, $theatre_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $shows = array();
    while($row = $result->fetch_assoc()) {
        $shows[] = $row;
    }
    
    echo json_encode($shows);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error fetching shows: ' . $e->getMessage()]);
}

$conn->close();
?> 