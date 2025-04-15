<?php
// Get server IP address
$serverIP = $_SERVER['SERVER_ADDR'];
if ($serverIP == '::1' || $serverIP == 'localhost' || $serverIP == '127.0.0.1') {
    // If localhost, try to get the actual IP
    $serverIP = gethostbyname(gethostname());
}

// Get all network interfaces
$interfaces = [];
if (function_exists('net_get_interfaces')) {
    $interfaces = net_get_interfaces();
}

// Output as JSON
header('Content-Type: application/json');
echo json_encode([
    'server_ip' => $serverIP,
    'server_name' => $_SERVER['SERVER_NAME'],
    'document_root' => $_SERVER['DOCUMENT_ROOT'],
    'interfaces' => $interfaces,
    'access_url' => 'http://' . $serverIP . '/movie-booking'
]);
?> 