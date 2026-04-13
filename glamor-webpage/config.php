// config.php
<?php
$servername = "localhost";
$username = "root";
$password = "40633920050122Ay@n"; // Default for XAMPP
$dbname = "salon_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>