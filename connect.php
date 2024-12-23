<?php
// Database connection settings
$host = 'localhost';  // Hostname (usually localhost)
$dbname = 'project';  // Your database name
$username = 'postgres';  // Your database username
$password = '1031';  // Your database password

try {
    // Create a new PDO instance (connection to the database)
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
    
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Catch any errors and display a message
    echo "Connection failed: " . $e->getMessage();
}
?>
