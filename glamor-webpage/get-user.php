<?php
session_start();
header('Content-Type: application/json');

include 'config.php';

// ✅ Fixed: get user_id from query string instead of session
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;

if (!$user_id) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Login required"]);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT UserID, FirstName, LastName, Email, Phone FROM Users WHERE UserID = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo json_encode([
            "success" => true,
            "user" => $user
        ]);
    } else {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "User not found"]);
    }
} catch (PDOException $e) {
    error_log("Error fetching user: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Failed to fetch user data"]);
}
?>