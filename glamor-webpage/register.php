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

$firstName       = isset($data['firstName'])       ? trim($data['firstName']) : '';
$lastName        = isset($data['lastName'])        ? trim($data['lastName'])  : '';
$email           = isset($data['email'])           ? trim($data['email'])     : '';
$phone           = isset($data['phone'])           ? trim($data['phone'])     : '';
$password        = isset($data['password'])        ? $data['password']        : '';
$confirmPassword = isset($data['confirmPassword']) ? $data['confirmPassword'] : '';

if (empty($firstName) || empty($lastName) || empty($email) || empty($phone) || empty($password)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "All fields are required"]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid email address"]);
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
    // ✅ Insert into Users table (login credentials)
    $stmt = $conn->prepare("INSERT INTO Users (Email, Password_Hash, Role) VALUES (?, ?, 'customer')");
    $stmt->execute([$email, $pass]);

    // ✅ Insert into Customers table (profile info) - linked by Email
    $stmt2 = $conn->prepare("INSERT INTO Customers (First_Name, Last_Name, Email, PhoneNumber) VALUES (?, ?, ?, ?)");
    $stmt2->execute([$firstName, $lastName, $email, $phone]);

    echo json_encode(["success" => true, "message" => "Registration successful! You can now log in."]);
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "An account with that email already exists"]);
    } else {
        error_log("Registration error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Registration failed: " . $e->getMessage()]);
    }
}
?>