<?php
include 'db_connect.php';

// We define the credentials here
$username = 'admin';
$password = 'password123';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Clear the old user and insert the fresh one
mysqli_query($conn, "DELETE FROM users WHERE username = '$username'");

$sql = "INSERT INTO users (username, password) VALUES ('$username', '$hashed_password')";

if(mysqli_query($conn, $sql)){
    echo "<h2>Success!</h2>";
    echo "Username: <b>admin</b><br>";
    echo "Password: <b>password123</b><br><br>";
    echo "The database has been updated with the correct hash. <br>";
    echo "<a href='login.php'>Go to Login Page</a>";
} else {
    echo "Error updating database: " . mysqli_error($conn);
}
?>