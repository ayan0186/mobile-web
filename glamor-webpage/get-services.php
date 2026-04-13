<?php
include 'config.php';
$result = $conn->query("SELECT id, name, price FROM Services");
$services = [];

while($row = $result->fetch_assoc()) {
    $services[] = $row;
}
echo json_encode($services);
?>
