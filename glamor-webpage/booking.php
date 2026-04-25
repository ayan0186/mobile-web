<?php
session_start();
header('Content-Type: application/json');

include 'config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Login required"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$user_id = $_SESSION['user_id'];
$date = isset($data['appointmentDate']) ? $data['appointmentDate'] : '';
$time = isset($data['appointmentTime']) ? $data['appointmentTime'] : '';
$beautician_id = isset($data['beauticianSelect']) && $data['beauticianSelect'] ? $data['beauticianSelect'] : null;

if (empty($date) || empty($time)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Date and time required"]);
    exit;
}

try {
    $stmt = $conn->prepare("INSERT INTO Appointments (UserID, BeauticianID, Appointment_Date, Appointment_Time, Status) VALUES (?, ?, ?, ?, ?)");
    $status = 'scheduled';
    $stmt->execute([$user_id, $beautician_id, $date, $time, $status]);
    
    echo json_encode(["success" => true, "message" => "Booking confirmed!"]);
} catch (PDOException $e) {
    error_log("Booking error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Booking failed: " . $e->getMessage()]);
}
?>
