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

$email    = isset($data['email'])    ? trim($data['email']) : '';
$password = isset($data['password']) ? $data['password']    : '';

if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Email and password required"]);
    exit;
}

if (!isset($conn) || !$conn) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}

try {
    // ✅ JOIN Users and Customers to get both UserID and CustomerID
    $stmt = $conn->prepare("
        SELECT u.UserID, u.Password_Hash, u.Role, c.CustomerID
        FROM Users u
        LEFT JOIN Customers c ON c.Email = u.Email
        WHERE u.Email = ?
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        if (password_verify($password, $user['Password_Hash'])) {
            $_SESSION['user_id']     = $user['UserID'];
            $_SESSION['customer_id'] = $user['CustomerID'];
            $_SESSION['role']        = $user['Role'];
            echo json_encode([
                "success"     => true,
                "user_id"     => $user['UserID'],
                "customer_id" => $user['CustomerID'], // ✅ needed for booking appointments
                "role"        => $user['Role']
            ]);
        } else {
            http_response_code(401);
            echo json_encode(["success" => false, "message" => "Invalid password"]);
        }
    } else {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "User not found"]);
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>