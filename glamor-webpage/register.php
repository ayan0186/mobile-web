<?php
session_start();
header('Content-Type: application/json');

ini_set('display_errors', 0);
error_reporting(E_ALL);

include 'config.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "No data received"]);
    exit;
}

$firstName = isset($data['firstName']) ? trim($data['firstName']) : '';
$lastName = isset($data['lastName']) ? trim($data['lastName']) : '';
$email = isset($data['email']) ? trim($data['email']) : '';
$phone = isset($data['phone']) ? trim($data['phone']) : '';
$password = isset($data['password']) ? $data['password'] : '';
$confirmPassword = isset($data['confirmPassword']) ? $data['confirmPassword'] : '';

// Validation
if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "First name, last name, email, and password are required"]);
    exit;
}

if ($password !== $confirmPassword) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Passwords do not match"]);
    exit;
}

if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Password must be at least 6 characters"]);
    exit;
}

$pass = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $conn->prepare("INSERT INTO Users (FirstName, LastName, Email, Phone, Password_Hash, Role) VALUES (?, ?, ?, ?, ?, ?)");
    $role = 'customer';
    $stmt->execute([$firstName, $lastName, $email, $phone, $pass, $role]);
    
    echo json_encode(["success" => true, "message" => "Registration successful! You can now log in."]);
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'UNIQUE constraint failed') !== false) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Email already exists"]);
    } else {
        error_log("Database error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Registration failed"]);
    }
}
?>
