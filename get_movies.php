<?php
header('Content-Type: application/json');

// Database connection
try {
    $pdo = new PDO('mysql:host=localhost;dbname=movie_booking', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

try {
    // Updated query to match the actual table structure
    $stmt = $pdo->query("SELECT id, title, language FROM movies ORDER BY id DESC");
    $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($movies); // Return just the movies array
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to fetch movies: ' . $e->getMessage()]);
}
?> 