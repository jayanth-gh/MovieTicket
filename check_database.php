<?php
header('Content-Type: application/json');

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";  // Default XAMPP password is empty
$dbname = "movie_booking";

try {
    // Create connection without selecting database
    $conn = new PDO("mysql:host=$servername", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if database exists
    $result = $conn->query("SHOW DATABASES LIKE '$dbname'");
    if ($result->rowCount() == 0) {
        // Create database
        $conn->exec("CREATE DATABASE $dbname");
        echo json_encode(['message' => 'Database created successfully']);
    }

    // Select the database
    $conn->exec("USE $dbname");

    // Check if tables exist
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $requiredTables = ['movies', 'shows', 'bookings'];
    $missingTables = array_diff($requiredTables, $tables);

    if (!empty($missingTables)) {
        // Create movies table
        if (!in_array('movies', $tables)) {
            $conn->exec("CREATE TABLE movies (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                duration INT,
                rating VARCHAR(10),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
        }

        // Create shows table
        if (!in_array('shows', $tables)) {
            $conn->exec("CREATE TABLE shows (
                id INT AUTO_INCREMENT PRIMARY KEY,
                movie_id INT NOT NULL,
                show_date DATE NOT NULL,
                show_time TIME NOT NULL,
                total_seats INT NOT NULL,
                booked_seats INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (movie_id) REFERENCES movies(id)
            )");
        }

        // Create bookings table
        if (!in_array('bookings', $tables)) {
            $conn->exec("CREATE TABLE bookings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                show_id INT NOT NULL,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                seats INT NOT NULL,
                booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (show_id) REFERENCES shows(id)
            )");
        }

        // Insert sample data if tables were just created
        if (count($missingTables) == count($requiredTables)) {
            // Insert sample movie
            $stmt = $conn->prepare("INSERT INTO movies (title, description, duration, rating) VALUES (?, ?, ?, ?)");
            $stmt->execute(['Sample Movie', 'A great movie to watch', 120, 'PG-13']);
            $movieId = $conn->lastInsertId();

            // Insert sample show
            $stmt = $conn->prepare("INSERT INTO shows (movie_id, show_date, show_time, total_seats) VALUES (?, ?, ?, ?)");
            $stmt->execute([$movieId, date('Y-m-d'), '19:00:00', 100]);
        }

        echo json_encode(['message' => 'Tables created successfully']);
    } else {
        echo json_encode(['message' => 'Database and tables are ready']);
    }
} catch(PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 