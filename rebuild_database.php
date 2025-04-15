<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

try {
    // Database connection details
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $dbname = 'movie_booking';

    // Create connection without database
    $conn = new mysqli($host, $username, $password);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Drop database if exists and create new one
    $conn->query("DROP DATABASE IF EXISTS $dbname");
    $conn->query("CREATE DATABASE $dbname");
    $conn->select_db($dbname);
    
    echo "Database '$dbname' created successfully<br>";
    
    // Create movies table
    $sql = "CREATE TABLE movies (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        genre VARCHAR(100),
        duration INT,
        language VARCHAR(50),
        release_date DATE,
        poster_url VARCHAR(255),
        description TEXT,
        rating DECIMAL(3,1),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Movies table created successfully<br>";
    } else {
        throw new Exception("Error creating movies table: " . $conn->error);
    }
    
    // Create theatres table
    $sql = "CREATE TABLE theatres (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        location VARCHAR(200) NOT NULL,
        total_screens INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Theatres table created successfully<br>";
    } else {
        throw new Exception("Error creating theatres table: " . $conn->error);
    }
    
    // Create users table
    $sql = "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('user', 'admin') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Users table created successfully<br>";
    } else {
        throw new Exception("Error creating users table: " . $conn->error);
    }
    
    // Create shows table with proper relationships
    $sql = "CREATE TABLE shows (
        id INT AUTO_INCREMENT PRIMARY KEY,
        movie_id INT NOT NULL,
        theatre_id INT NOT NULL,
        show_date DATE NOT NULL,
        show_time TIME NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        total_seats INT NOT NULL DEFAULT 100,
        booked_seats INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
        FOREIGN KEY (theatre_id) REFERENCES theatres(id) ON DELETE CASCADE
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Shows table created successfully<br>";
    } else {
        throw new Exception("Error creating shows table: " . $conn->error);
    }
    
    // Create bookings table
    $sql = "CREATE TABLE bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        show_id INT NOT NULL,
        num_seats INT NOT NULL,
        total_price DECIMAL(10,2) NOT NULL,
        booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('confirmed', 'cancelled') DEFAULT 'confirmed',
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (show_id) REFERENCES shows(id) ON DELETE CASCADE
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Bookings table created successfully<br>";
    } else {
        throw new Exception("Error creating bookings table: " . $conn->error);
    }
    
    // Insert sample movies
    $movies = "INSERT INTO movies (title, genre, duration, language, release_date, poster_url, description, rating) VALUES 
        ('Inception', 'Sci-Fi', 148, 'English', '2024-04-15', 'https://example.com/inception.jpg', 'A thief who steals corporate secrets through dream-sharing technology is given the inverse task of planting an idea into the mind of a C.E.O.', 8.8),
        ('The Dark Knight', 'Action', 152, 'English', '2024-04-16', 'https://example.com/dark-knight.jpg', 'When the menace known as the Joker wreaks havoc and chaos on the people of Gotham, Batman must accept one of the greatest psychological and physical tests of his ability to fight injustice.', 9.0),
        ('Interstellar', 'Sci-Fi', 169, 'English', '2024-04-17', 'https://example.com/interstellar.jpg', 'A team of explorers travel through a wormhole in space in an attempt to ensure humanity\'s survival.', 8.6),
        ('RRR', 'Action', 182, 'Telugu', '2024-04-18', 'https://example.com/rrr.jpg', 'A tale of two legendary revolutionaries and their journey far away from home.', 8.5),
        ('Baahubali', 'Action', 159, 'Telugu', '2024-04-19', 'https://example.com/baahubali.jpg', 'In ancient India, an adventurous and daring man becomes involved in a decades-old war between two warring peoples.', 8.2)";
    
    if ($conn->query($movies) === TRUE) {
        echo "Sample movies added successfully<br>";
    } else {
        throw new Exception("Error adding sample movies: " . $conn->error);
    }
    
    // Insert sample theatres
    $theatres = "INSERT INTO theatres (name, location, total_screens) VALUES 
        ('PVR Cinemas', 'City Center Mall', 4),
        ('INOX Movies', 'Downtown Plaza', 3),
        ('Cinepolis', 'Metro Mall', 5),
        ('SPI Cinemas', 'Central Square', 3),
        ('Sathyam Cinemas', 'Entertainment Hub', 6)";
    
    if ($conn->query($theatres) === TRUE) {
        echo "Sample theatres added successfully<br>";
    } else {
        throw new Exception("Error adding sample theatres: " . $conn->error);
    }
    
    // Insert sample users (including admin)
    $users = "INSERT INTO users (name, email, password, role) VALUES 
        ('Admin User', 'admin@moviehub.com', '" . password_hash('admin123', PASSWORD_DEFAULT) . "', 'admin'),
        ('John Doe', 'john@example.com', '" . password_hash('user123', PASSWORD_DEFAULT) . "', 'user'),
        ('Jane Smith', 'jane@example.com', '" . password_hash('user123', PASSWORD_DEFAULT) . "', 'user')";
    
    if ($conn->query($users) === TRUE) {
        echo "Sample users added successfully<br>";
    } else {
        throw new Exception("Error adding sample users: " . $conn->error);
    }
    
    // Get movie IDs
    $movies = $conn->query("SELECT id FROM movies");
    $movieIds = [];
    while($movie = $movies->fetch_assoc()) {
        $movieIds[] = $movie['id'];
    }
    
    // Get theatre IDs
    $theatres = $conn->query("SELECT id FROM theatres");
    $theatreIds = [];
    while($theatre = $theatres->fetch_assoc()) {
        $theatreIds[] = $theatre['id'];
    }
    
    // Insert sample shows
    $dates = ['2024-04-15', '2024-04-16', '2024-04-17', '2024-04-18', '2024-04-19', '2024-04-20', '2024-04-21'];
    $times = ['10:00:00', '13:00:00', '16:00:00', '19:00:00', '22:00:00'];
    $prices = [250, 300, 350, 400, 450];
    
    foreach ($movieIds as $movieId) {
        foreach ($theatreIds as $theatreId) {
            foreach ($dates as $date) {
                foreach ($times as $index => $time) {
                    $price = $prices[$index % count($prices)];
                    $sql = "INSERT INTO shows (movie_id, theatre_id, show_date, show_time, price) 
                            VALUES ($movieId, $theatreId, '$date', '$time', $price)";
                    $conn->query($sql);
                }
            }
        }
    }
    echo "Sample shows added successfully<br>";
    
    // Insert sample bookings
    $users = $conn->query("SELECT id FROM users WHERE role = 'user'");
    $userIds = [];
    while($user = $users->fetch_assoc()) {
        $userIds[] = $user['id'];
    }
    
    $shows = $conn->query("SELECT id FROM shows LIMIT 10");
    $showIds = [];
    while($show = $shows->fetch_assoc()) {
        $showIds[] = $show['id'];
    }
    
    foreach ($userIds as $userId) {
        foreach ($showIds as $showId) {
            $numSeats = rand(1, 4);
            $show = $conn->query("SELECT price FROM shows WHERE id = $showId")->fetch_assoc();
            $totalPrice = $numSeats * $show['price'];
            
            $sql = "INSERT INTO bookings (user_id, show_id, num_seats, total_price) 
                    VALUES ($userId, $showId, $numSeats, $totalPrice)";
            $conn->query($sql);
        }
    }
    echo "Sample bookings added successfully<br>";
    
    echo json_encode([
        'success' => true,
        'message' => 'Database rebuilt successfully with all tables and sample data'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?> 