<?php
session_start();
header('Content-Type: application/json');

// Check if admin is logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    echo json_encode(['logged_in' => true]);
} else {
    echo json_encode(['logged_in' => false]);
}
?> 