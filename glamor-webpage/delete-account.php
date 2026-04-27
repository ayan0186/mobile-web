<?php
session_start();
header('Content-Type: application/json');

include 'config.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['password'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Password confirmation is required"]);
    exit;
}

$user_id     = isset($data['user_id'])     ? (int)$data['user_id']     : null;
$customer_id = isset($data['customer_id']) ? (int)$data['customer_id'] : null;

if (!$user_id) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Login required"]);
    exit;
}

$passwordConfirm = $data['password'];

try {
    $stmt = $conn->prepare("SELECT Password_Hash FROM Users WHERE UserID = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "User not found"]);
        exit;
    }
    
    if (!password_verify($passwordConfirm, $user['Password_Hash'])) {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Incorrect password"]);
        exit;
    }
    
    // ✅ Delete appointments by customer_id
    if ($customer_id) {
        $stmt = $conn->prepare("DELETE FROM Appointments WHERE CustomerID = ?");
        $stmt->execute([$customer_id]);
    }

    // ✅ Delete from Customers by customer_id
    if ($customer_id) {
        $stmt = $conn->prepare("DELETE FROM Customers WHERE CustomerID = ?");
        $stmt->execute([$customer_id]);
    }
    
    // Delete from Users
    $stmt = $conn->prepare("DELETE FROM Users WHERE UserID = ?");
    $stmt->execute([$user_id]);
    
    session_destroy();
    
    echo json_encode(["success" => true, "message" => "Account deleted successfully"]);
} catch (PDOException $e) {
    error_log("Error deleting account: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Failed to delete account"]);
}
?>