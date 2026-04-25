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

if (!isset($data['appointmentId'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Appointment ID required"]);
    exit;
}

$appointment_id = $data['appointmentId'];
$user_id = $_SESSION['user_id'];

try {
    // Verify the appointment belongs to this user before deleting
    $stmt = $conn->prepare("SELECT UserID FROM Appointments WHERE AppointmentID = ?");
    $stmt->execute([$appointment_id]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$appointment) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Appointment not found"]);
        exit;
    }
    
    if ($appointment['UserID'] !== $user_id) {
        http_response_code(403);
        echo json_encode(["success" => false, "message" => "Not authorized to cancel this appointment"]);
        exit;
    }
    
    // Delete the appointment
    $stmt = $conn->prepare("DELETE FROM Appointments WHERE AppointmentID = ?");
    $stmt->execute([$appointment_id]);
    
    echo json_encode([
        "success" => true,
        "message" => "Appointment cancelled successfully"
    ]);
} catch (PDOException $e) {
    error_log("Error cancelling appointment: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Failed to cancel appointment"]);
}
?>
