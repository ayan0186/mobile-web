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

if (!$data || !isset($data['appointmentId'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Appointment ID is required"]);
    exit;
}

$appointmentId = (int)$data['appointmentId'];
$userId = $_SESSION['user_id'];

try {
    // Verify the appointment belongs to the current user before deleting
    $checkStmt = $conn->prepare("SELECT AppointmentID FROM Appointments WHERE AppointmentID = ? AND UserID = ?");
    $checkStmt->execute([$appointmentId, $userId]);
    
    if (!$checkStmt->fetch()) {
        http_response_code(403);
        echo json_encode(["success" => false, "message" => "You do not have permission to delete this appointment"]);
        exit;
    }
    
    // Delete the appointment
    $deleteStmt = $conn->prepare("DELETE FROM Appointments WHERE AppointmentID = ?");
    $deleteStmt->execute([$appointmentId]);
    
    echo json_encode(["success" => true, "message" => "Appointment deleted successfully"]);
    
} catch (PDOException $e) {
    error_log("Error deleting appointment: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Failed to delete appointment"]);
}
?>
