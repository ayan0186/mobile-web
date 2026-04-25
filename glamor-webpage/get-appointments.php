<?php
session_start();
header('Content-Type: application/json');

include 'config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Login required"]);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $conn->prepare("SELECT * FROM Appointments WHERE UserID = ? ORDER BY Appointment_Date DESC");
    $stmt->execute([$user_id]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        "success" => true,
        "appointments" => $appointments
    ]);
} catch (PDOException $e) {
    error_log("Error fetching appointments: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Failed to fetch appointments"]);
}
?>
