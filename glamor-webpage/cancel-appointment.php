<?php
session_start();
header('Content-Type: application/json');

include 'config.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['appointmentId'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Appointment ID required"]);
    exit;
}

// ✅ Use customer_id
$customer_id    = isset($data['customer_id']) ? (int)$data['customer_id'] : null;
$appointment_id = (int)$data['appointmentId'];

if (!$customer_id) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Login required"]);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT CustomerID FROM Appointments WHERE AppointmentID = ?");
    $stmt->execute([$appointment_id]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$appointment) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Appointment not found"]);
        exit;
    }
    
    if ((int)$appointment['CustomerID'] !== $customer_id) {
        http_response_code(403);
        echo json_encode(["success" => false, "message" => "Not authorized to cancel this appointment"]);
        exit;
    }
    
    $stmt = $conn->prepare("DELETE FROM Appointments WHERE AppointmentID = ?");
    $stmt->execute([$appointment_id]);
    
    echo json_encode(["success" => true, "message" => "Appointment cancelled successfully"]);
} catch (PDOException $e) {
    error_log("Error cancelling appointment: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Failed to cancel appointment"]);
}
?>