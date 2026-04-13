<?php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Login required"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

// Get current CustomerID from session (set during login)
$customer_id = $_SESSION['customer_id']; 
$date = $data['appointmentDate'];
$time = $data['appointmentTime'];
$beautician_id = $data['beauticianSelect'] ?: null; // Optional field

$stmt = $conn->prepare("INSERT INTO Appointments (CustomerID, BeauticianID, Appointment_Date, Appointment_Time, Status) VALUES (?, ?, ?, ?, 'scheduled')");
$stmt->bind_param("iiss", $customer_id, $beautician_id, $date, $time);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Booking confirmed!"]);
} else {
    echo json_encode(["success" => false, "message" => "Booking failed"]);
}
?>
