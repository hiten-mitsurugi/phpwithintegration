<?php
include './connects.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize user inputs
    $fname = filter_input(INPUT_POST, 'firstname', FILTER_SANITIZE_STRING);
    $lname = filter_input(INPUT_POST, 'lastname', FILTER_SANITIZE_STRING);
    $mname = filter_input(INPUT_POST, 'middlename', FILTER_SANITIZE_STRING);
    $contactnumber = filter_input(INPUT_POST, 'contactnumber', FILTER_SANITIZE_STRING);
    $addressRegion = filter_input(INPUT_POST, 'sRegion', FILTER_SANITIZE_STRING);
    $addressProvince = filter_input(INPUT_POST, 'sProvince', FILTER_SANITIZE_STRING);
    $addressCity = filter_input(INPUT_POST, 'sCity', FILTER_SANITIZE_STRING);
    $addressBarangay = filter_input(INPUT_POST, 'barangay', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
    $gender = filter_input(INPUT_POST, 'gender', FILTER_SANITIZE_STRING);

    // Validate inputs
    if (!$fname || !$lname || !$contactnumber || !$addressRegion || !$addressProvince || 
        !$addressCity || !$addressBarangay || !$email || !$username || !$password || !$gender) {
        echo "All required fields must be filled";
        exit();
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Call the PostgreSQL function
    $query = "SELECT register_user($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12)";
    $result = pg_query_params($connect, $query, array(
        $username, $email, $hashed_password, $contactnumber, $fname, $mname, $lname,
        $addressRegion, $addressProvince, $addressCity, $addressBarangay, $gender
    ));

    if ($result) {
        $row = pg_fetch_row($result);
        echo $row[0];  // This will be the message returned by the function
    } else {
        echo "Error calling registration function: " . pg_last_error($connect);
    }
}
?>