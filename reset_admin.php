<?php
include 'db_connect.php';

// Credentials
$username = 'admin';
$password = 'password123';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// 1. Delete existing to avoid 'Duplicate Entry' error
mysqli_query($conn, "DELETE FROM users WHERE username = '$username'");

// 2. Insert with the 'admin' role explicitly set
$sql = "INSERT INTO users (username, password, role) VALUES ('$username', '$hashed_password', 'admin')";

if(mysqli_query($conn, $sql)){
    echo "<div style='font-family: Arial; padding: 20px; border: 1px solid #d2a679; background: #fff8e1; max-width: 400px; margin: 50px auto;'>";
    echo "<h2 style='color: #5d4037;'>✅ Admin Account Restored</h2>";
    echo "User: <b>$username</b><br>";
    echo "Pass: <b>$password</b><br><br>";
    echo "<a href='login.php' style='background: #8b4513; color: white; padding: 10px; text-decoration: none; border-radius: 4px;'>Back to Login</a>";
    echo "</div>";
} else {
    echo "Error: " . mysqli_error($conn);
}
?>