<?php
header('Content-Type: application/json');
include '../connection.php';

try {
    // Create theatres table
    $sql = "CREATE TABLE IF NOT EXISTS theatres (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        location VARCHAR(200) NOT NULL,
        total_screens INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql) === TRUE) {
        // Check if theatres table is empty
        $check = $conn->query("SELECT COUNT(*) as count FROM theatres");
        $row = $check->fetch_assoc();
        
        if ($row['count'] == 0) {
            // Insert sample theatres
            $theatres = "INSERT INTO theatres (name, location, total_screens) VALUES 
                ('PVR Cinemas', 'City Center Mall', 4),
                ('INOX Movies', 'Downtown Plaza', 3),
                ('Cinepolis', 'Metro Mall', 5)";
            
            if ($conn->query($theatres) === TRUE) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Theatres table created and sample data added successfully'
                ]);
            } else {
                throw new Exception("Error adding sample theatres: " . $conn->error);
            }
        } else {
            echo json_encode([
                'success' => true,
                'message' => 'Theatres table already exists with data'
            ]);
        }
    } else {
        throw new Exception("Error creating theatres table: " . $conn->error);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?> 