<?php
// Database configuration using SQLite
try {
    $dbfile = __DIR__ . '/salon.db';
    
    $conn = new PDO(
        "sqlite:" . $dbfile,
        null,
        null,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Create Users table if it doesn't exist
    $conn->exec("CREATE TABLE IF NOT EXISTS Users (
        UserID INTEGER PRIMARY KEY AUTOINCREMENT,
        FirstName TEXT,
        LastName TEXT,
        Email TEXT UNIQUE NOT NULL,
        Phone TEXT,
        Password_Hash TEXT NOT NULL,
        Role TEXT DEFAULT 'customer'
    )");
    
    // Create Appointments table if it doesn't exist
    $conn->exec("CREATE TABLE IF NOT EXISTS Appointments (
        AppointmentID INTEGER PRIMARY KEY AUTOINCREMENT,
        UserID INTEGER NOT NULL,
        BeauticianID INTEGER,
        Appointment_Date TEXT NOT NULL,
        Appointment_Time TEXT NOT NULL,
        Status TEXT DEFAULT 'scheduled',
        FOREIGN KEY(UserID) REFERENCES Users(UserID)
    )");
    
} catch (PDOException $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed: " . $e->getMessage()
    ]);
    exit;
}
?>