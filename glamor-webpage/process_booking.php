<?php
session_start();
header('Content-Type: application/json');

include 'config.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "No data received"]);
    exit;
}

// ✅ Use customer_id since Appointments table uses CustomerID
$customer_id   = isset($data['customer_id']) ? (int)$data['customer_id'] : null;
$date          = isset($data['appointmentDate'])  ? trim($data['appointmentDate'])  : '';
$time          = isset($data['appointmentTime'])  ? trim($data['appointmentTime'])  : '';
$beautician_id = isset($data['beauticianSelect']) && $data['beauticianSelect'] ? (int)$data['beauticianSelect'] : null;

if (!$customer_id) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Login required"]);
    exit;
}

if (empty($date) || empty($time)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Date and time are required"]);
    exit;
}

try {
    $stmt = $conn->prepare("INSERT INTO Appointments (CustomerID, BeauticianID, Appointment_Date, Appointment_Time, Status) 
                            VALUES (?, ?, ?, ?, 'scheduled')");
    $stmt->execute([$customer_id, $beautician_id, $date, $time]);
    
    echo json_encode([
        "success"       => true,
        "message"       => "Appointment booked successfully",
        "appointmentId" => $conn->lastInsertId()
    ]);
} catch (PDOException $e) {
    error_log("Error booking appointment: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Failed to book appointment: " . $e->getMessage()]);
}
?>