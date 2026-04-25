<?php
session_start();
header('Content-Type: application/json');

// Suppress PHP warnings from showing in response
ini_set('display_errors', 0);
error_reporting(E_ALL);

include 'config.php';

$data = json_decode(file_get_contents("php://input"), true);

// Check if data was received
if (!$data) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "No data received"]);
    exit;
}

$email = isset($data['email']) ? trim($data['email']) : '';
$password = isset($data['password']) ? $data['password'] : '';

// Debug logging
error_log("Login attempt: " . $email);

if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Email and password required"]);
    exit;
}

// Check if connection exists
if (!isset($conn) || !$conn) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT UserID, Password_Hash, Role FROM Users WHERE Email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Verifies the hashed password from your Signup
        if (password_verify($password, $user['Password_Hash'])) {
            $_SESSION['user_id'] = $user['UserID'];
            $_SESSION['role'] = $user['Role'];
            echo json_encode([
                "success" => true, 
                "user_id" => $user['UserID'],
                "role" => $user['Role']
            ]);
        } else {
            error_log("Password verification failed for: " . $email);
            http_response_code(401);
            echo json_encode(["success" => false, "message" => "Invalid password"]);
        }
    } else {
        error_log("User not found: " . $email);
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "User not found"]);
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database error"]);
}
?>
