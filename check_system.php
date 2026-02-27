<?php
session_start();
include 'db_connect.php';

echo "<h2>Shokher Ghor System Check</h2>";

// 1. Check Database Connection
if ($conn) {
    echo "<p style='color:green;'>✅ Database Connected Successfully.</p>";
} else {
    echo "<p style='color:red;'>❌ Database Connection Failed: " . mysqli_connect_error() . "</p>";
}

// 2. Check Session Status
if (isset($_SESSION['admin'])) {
    echo "<p style='color:green;'>✅ Session Active (Logged in as: " . $_SESSION['admin'] . ").</p>";
} else {
    echo "<p style='color:orange;'>⚠️ No Active Session. You need to login via login.php.</p>";
}

// 3. Check Table Structure
$tables = ['products', 'users', 'inventory_logs'];
foreach ($tables as $table) {
    $check = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    if (mysqli_num_rows($check) > 0) {
        echo "<p style='color:green;'>✅ Table '$table' exists.</p>";
    } else {
        echo "<p style='color:red;'>❌ Table '$table' is missing!</p>";
    }
}

// 4. Check Uploads Folder
if (is_dir('uploads') && is_writable('uploads')) {
    echo "<p style='color:green;'>✅ 'uploads' folder is ready and writable.</p>";
} else {
    echo "<p style='color:red;'>❌ 'uploads' folder is missing or not writable.</p>";
}
?>