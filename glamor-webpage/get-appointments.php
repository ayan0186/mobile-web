<?php
session_start();
header('Content-Type: application/json');

include 'config.php';

// ✅ Use customer_id since Appointments uses CustomerID
$customer_id = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : null;

if (!$customer_id) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Login required"]);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT * FROM Appointments WHERE CustomerID = ? ORDER BY Appointment_Date DESC");
    $stmt->execute([$customer_id]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(["success" => true, "appointments" => $appointments]);
} catch (PDOException $e) {
    error_log("Error fetching appointments: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Failed to fetch appointments"]);
}
?>