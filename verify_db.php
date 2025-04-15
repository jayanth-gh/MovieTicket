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
    // Create connection
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully\n\n";
    
    // Check bookings table structure
    echo "Bookings table structure:\n";
    $stmt = $conn->query("DESCRIBE bookings");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $column) {
        echo "Field: " . $column['Field'] . "\n";
        echo "Type: " . $column['Type'] . "\n";
        echo "Null: " . $column['Null'] . "\n";
        echo "Key: " . $column['Key'] . "\n";
        echo "Default: " . $column['Default'] . "\n";
        echo "Extra: " . $column['Extra'] . "\n";
        echo "-------------------\n";
    }
    
    // Check if there are any existing bookings
    $stmt = $conn->query("SELECT COUNT(*) FROM bookings");
    echo "\nNumber of existing bookings: " . $stmt->fetchColumn() . "\n";
    
    // Check shows table structure
    echo "\nShows table structure:\n";
    $stmt = $conn->query("DESCRIBE shows");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $column) {
        echo "Field: " . $column['Field'] . "\n";
        echo "Type: " . $column['Type'] . "\n";
        echo "Null: " . $column['Null'] . "\n";
        echo "Key: " . $column['Key'] . "\n";
        echo "Default: " . $column['Default'] . "\n";
        echo "Extra: " . $column['Extra'] . "\n";
        echo "-------------------\n";
    }
    
    // Check if there are any existing shows
    $stmt = $conn->query("SELECT COUNT(*) FROM shows");
    echo "\nNumber of existing shows: " . $stmt->fetchColumn() . "\n";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Error code: " . $e->getCode() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?> 