<?php
session_start();
header('Content-Type: application/json');

include 'config.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['appointmentId']) || !isset($data['date']) || !isset($data['time'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit;
}

// ✅ Use customer_id
$customer_id   = isset($data['customer_id']) ? (int)$data['customer_id'] : null;
$appointmentId = (int)$data['appointmentId'];
$newDate       = trim($data['date']);
$newTime       = trim($data['time']);

if (!$customer_id) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Login required"]);
    exit;
}

if (empty($newDate) || empty($newTime)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Date and time cannot be empty"]);
    exit;
}

try {
    $checkStmt = $conn->prepare("SELECT AppointmentID FROM Appointments WHERE AppointmentID = ? AND CustomerID = ?");
    $checkStmt->execute([$appointmentId, $customer_id]);
    
    if (!$checkStmt->fetch()) {
        http_response_code(403);
        echo json_encode(["success" => false, "message" => "You do not have permission to update this appointment"]);
        exit;
    }
    
    $updateStmt = $conn->prepare("UPDATE Appointments SET Appointment_Date = ?, Appointment_Time = ? WHERE AppointmentID = ?");
    $updateStmt->execute([$newDate, $newTime, $appointmentId]);
    
    echo json_encode(["success" => true, "message" => "Appointment rescheduled successfully"]);
} catch (PDOException $e) {
    error_log("Error updating appointment: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Failed to reschedule appointment"]);
}
?>