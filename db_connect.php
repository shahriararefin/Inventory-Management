<?php
$host = "localhost";
$user = "root";
$pass = ""; 
$dbname = "shokher_ghor_db";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// 1. Set Charset for Unicode support (Crucial for a shop in Bangladesh)
mysqli_set_charset($conn, "utf8mb4");

// 2. Fetch Global Shop Settings
$settings_res = mysqli_query($conn, "SELECT * FROM shop_settings WHERE id = 1");
$shop = mysqli_fetch_assoc($settings_res);

// 3. Define Constants with Fallbacks
// We use defined() checks to prevent "Constant already defined" notices
if (!defined('SHOP_NAME')) {
    define('SHOP_NAME', $shop['shop_name'] ?? 'Shokher Ghor');
}

if (!defined('CURRENCY')) {
    define('CURRENCY', $shop['currency'] ?? 'BDT');
}

if (!defined('TAX_RATE')) {
    define('TAX_RATE', $shop['tax_rate'] ?? 0); 
}
?>