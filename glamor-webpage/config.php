<?php
$host     = '127.0.0.1';
$dbname   = 'salon_db';   // ← replace with your actual MySQL database name
$username = 'root';       // ← replace with your MySQL username
$password = '40633920050122Ay@n';           // ← replace with your MySQL password

try {
    $conn = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed: " . $e->getMessage()
    ]);
    exit;
}
?>