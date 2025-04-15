<?php
header('Content-Type: application/json');
include '../connection.php';

try {
    $sql = "SELECT id, name, location FROM theatres ORDER BY name";
    $result = $conn->query($sql);
    
    $theatres = array();
    while($row = $result->fetch_assoc()) {
        $theatres[] = $row;
    }
    
    echo json_encode($theatres);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error fetching theatres: ' . $e->getMessage()]);
}

$conn->close();
?> 