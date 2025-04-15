<?php
header('Content-Type: application/json');
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Database connection
try {
    $pdo = new PDO('mysql:host=localhost;dbname=movie_booking', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add':
        try {
            $stmt = $pdo->prepare("INSERT INTO movies (title, genre, duration, language, release_date, poster_url) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['title'],
                $_POST['genre'],
                $_POST['duration'],
                $_POST['language'],
                $_POST['release_date'],
                $_POST['poster_url'] ?? null
            ]);
            echo json_encode(['success' => true, 'message' => 'Movie added successfully']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to add movie: ' . $e->getMessage()]);
        }
        break;

    case 'edit':
        try {
            $stmt = $pdo->prepare("UPDATE movies SET title = ?, genre = ?, duration = ?, language = ?, release_date = ?, poster_url = ? WHERE id = ?");
            $stmt->execute([
                $_POST['title'],
                $_POST['genre'],
                $_POST['duration'],
                $_POST['language'],
                $_POST['release_date'],
                $_POST['poster_url'] ?? null,
                $_POST['id']
            ]);
            echo json_encode(['success' => true, 'message' => 'Movie updated successfully']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to update movie: ' . $e->getMessage()]);
        }
        break;

    case 'delete':
        try {
            $stmt = $pdo->prepare("DELETE FROM movies WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            echo json_encode(['success' => true, 'message' => 'Movie deleted successfully']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to delete movie: ' . $e->getMessage()]);
        }
        break;

    case 'get_all':
        try {
            $stmt = $pdo->query("SELECT * FROM movies ORDER BY release_date DESC");
            $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'movies' => $movies]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to fetch movies: ' . $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>
