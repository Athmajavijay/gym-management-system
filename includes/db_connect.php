<?php
/**
 * Database Connection File
 * FitZone Gym Management System
 * 
 * This file handles the connection to the MySQL database
 */

// Database configuration
define('DB_HOST', 'localhost:3307');
define('DB_USER', 'root');
define('DB_PASS', '');  // Default XAMPP password is empty
define('DB_NAME', 'gym_fitzone');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to UTF-8
$conn->set_charset("utf8mb4");

// Optional: Uncomment to see connection success message during development
// echo "Connected successfully to database!";
?>