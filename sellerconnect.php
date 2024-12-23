<?php
$host = 'localhost';  // Adjust with your database host
$dbname = 'project';
$user = 'seller_user';
$password = 'Password123!';

// Attempt to establish the connection
$connect = pg_connect("host=$host dbname=$dbname user=$user password=$password");

// Check if the connection was successful
if ($connect) {
    echo "";
} else {
    die("Error in connection: " . pg_last_error());
}
?>
