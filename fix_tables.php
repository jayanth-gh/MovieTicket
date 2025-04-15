<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers
header('Content-Type: text/plain');

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";  // Default XAMPP password is empty
$dbname = "movie_booking";

try {
    // First connect without database
    $conn = new PDO("mysql:host=$servername", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to MySQL server successfully\n";
    
    // Check if database exists
    $stmt = $conn->query("SHOW DATABASES LIKE '$dbname'");
    if ($stmt->rowCount() == 0) {
        echo "Database '$dbname' does not exist. Creating it...\n";
        $conn->exec("CREATE DATABASE $dbname");
        echo "Database created successfully\n";
    }
    
    // Now connect to the specific database
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully\n";
    
    // Check if movies table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'movies'");
    if ($stmt->rowCount() == 0) {
        echo "Movies table does not exist. Creating it...\n";
        $conn->exec("CREATE TABLE movies (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            language VARCHAR(50) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        echo "Movies table created successfully\n";
    }
    
    // Check if shows table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'shows'");
    if ($stmt->rowCount() == 0) {
        echo "Shows table does not exist. Creating it...\n";
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
        echo "Shows table created successfully\n";
    }
    
    // Drop existing bookings table if it exists
    $stmt = $conn->query("SHOW TABLES LIKE 'bookings'");
    if ($stmt->rowCount() > 0) {
        echo "Dropping existing bookings table...\n";
        $conn->exec("DROP TABLE bookings");
        echo "Bookings table dropped successfully\n";
    }
    
    // Create new bookings table with correct structure
    echo "Creating new bookings table...\n";
    $conn->exec("CREATE TABLE bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        show_id INT NOT NULL,
        customer_name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        seats INT NOT NULL,
        booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (show_id) REFERENCES shows(id)
    )");
    echo "Bookings table created successfully with all required columns\n";
    
    // Insert sample data if tables are empty
    $stmt = $conn->query("SELECT COUNT(*) FROM movies");
    if ($stmt->fetchColumn() == 0) {
        echo "Inserting sample movies...\n";
        $conn->exec("INSERT INTO movies (title, language) VALUES 
            ('The Dark Knight', 'English'),
            ('Inception', 'English'),
            ('Parasite', 'Korean')
        ");
        echo "Sample movies inserted successfully\n";
    }
    
    $stmt = $conn->query("SELECT COUNT(*) FROM shows");
    if ($stmt->fetchColumn() == 0) {
        echo "Inserting sample shows...\n";
        $conn->exec("INSERT INTO shows (movie_id, show_date, show_time, total_seats) VALUES 
            (1, CURDATE(), '14:00:00', 100),
            (1, CURDATE(), '18:00:00', 100),
            (2, CURDATE(), '16:00:00', 100),
            (3, CURDATE(), '20:00:00', 100)
        ");
        echo "Sample shows inserted successfully\n";
    }
    
    // Verify table structures
    echo "\nVerifying table structures...\n";
    
    echo "\nMovies table structure:\n";
    $stmt = $conn->query("DESCRIBE movies");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($columns as $column) {
        echo "- " . $column . "\n";
    }
    
    echo "\nShows table structure:\n";
    $stmt = $conn->query("DESCRIBE shows");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($columns as $column) {
        echo "- " . $column . "\n";
    }
    
    echo "\nBookings table structure:\n";
    $stmt = $conn->query("DESCRIBE bookings");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($columns as $column) {
        echo "- " . $column . "\n";
    }
    
    echo "\nDatabase structure fixed successfully!";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Error code: " . $e->getCode() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?> 