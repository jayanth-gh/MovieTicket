<?php
include '../connection.php';

// Add theatre_id column to shows table
$sql = "ALTER TABLE shows 
        ADD COLUMN IF NOT EXISTS theatre_id INT,
        ADD FOREIGN KEY (theatre_id) REFERENCES theatres(id)";

if ($conn->query($sql) === TRUE) {
    echo "Shows table updated successfully<br>";
    
    // Update existing shows with theatre information
    $update = "UPDATE shows SET theatre_id = 1 WHERE theatre_id IS NULL";
    if ($conn->query($update) === TRUE) {
        echo "Existing shows updated with theatre information";
    } else {
        echo "Error updating shows: " . $conn->error;
    }
} else {
    echo "Error updating shows table: " . $conn->error;
}

$conn->close();
?> 