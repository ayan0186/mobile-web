<?php
// process_booking.php
include 'config.php'; // Must contain session_start() and $conn

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: sign-in.html"); // Redirect if not logged in
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get values from the form's 'name' attributes
    $customer_id = $_SESSION['user_id']; 
    $date = $_POST['appointmentDate'];
    $time = $_POST['appointmentTime'];
    $beautician_id = !empty($_POST['beauticianSelect']) ? $_POST['beauticianSelect'] : NULL;

    // Use exact column names from your 'DESCRIBE Appointments' output
    $sql = "INSERT INTO Appointments (CustomerID, BeauticianID, Appointment_Date, Appointment_Time, Status) 
            VALUES (?, ?, ?, ?, 'scheduled')";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiss", $customer_id, $beautician_id, $date, $time);

    if ($stmt->execute()) {
        // Redirect to dashboard on success
        header("Location: dashboard.html?status=success");
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
