<?php
include 'config.php';

$data = json_decode(file_get_contents("php://input"), true); // Get data from JS

$fname = $data['firstName'];
$lname = $data['lastName'];
$email = $data['email'];
$pass  = password_hash($data['password'], PASSWORD_DEFAULT); // Secure password

$stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $fname, $lname, $email, $pass);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Email already exists"]);
}
?>
