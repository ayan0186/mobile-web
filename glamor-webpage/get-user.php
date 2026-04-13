<?php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false]);
    exit;
}

echo json_encode([
    "success" => true,
    "first_name" => $_SESSION['first_name']
]);
?>
