<?php
session_start();
if(!isset($_SESSION['admin'])){ header("Location: login.php"); exit(); }
include 'db_connect.php';

$message = "";

if(isset($_POST['update_pass'])){
    $user_id = $_SESSION['user_id'];
    $old_pass = $_POST['old_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    // 1. Fetch current hashed password from DB
    $res = mysqli_query($conn, "SELECT password FROM users WHERE id = $user_id");
    $user = mysqli_fetch_assoc($res);

    // 2. Validation Logic
    if (!password_verify($old_pass, $user['password'])) {
        $message = "<p style='color:red;'>❌ Current password is incorrect.</p>";
    } elseif ($new_pass !== $confirm_pass) {
        $message = "<p style='color:red;'>❌ New passwords do not match.</p>";
    } elseif (strlen($new_pass) < 6) {
        $message = "<p style='color:red;'>❌ New password must be at least 6 characters.</p>";
    } else {
        // 3. Update Password
        $hashed_new = password_hash($new_pass, PASSWORD_DEFAULT);
        $update_sql = "UPDATE users SET password = '$hashed_new' WHERE id = $user_id";
        
        if(mysqli_query($conn, $update_sql)){
            $message = "<p style='color:green;'>✅ Password updated successfully!</p>";
        } else {
            $message = "<p style='color:red;'>❌ Database error.</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password - <?php echo SHOP_NAME; ?></title>
    <style>
        body { font-family: 'Segoe UI', Arial; background: #f4f7f6; padding: 20px; }
        .form-card { background: white; padding: 30px; border-radius: 8px; max-width: 400px; margin: 50px auto; box-shadow: 0 4px 10px rgba(0,0,0,0.1); border-top: 5px solid #8b4513; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        label { font-size: 14px; font-weight: bold; color: #5d4037; }
        button { width: 100%; padding: 12px; background: #8b4513; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; margin-top: 10px; }
        button:hover { background: #5d4037; }
    </style>
</head>
<body>

<?php include 'navigation.php'; ?>

<div class="form-card">
    <h2 style="color: #8b4513; margin-top: 0;">🔐 Change Password</h2>
    <?php echo $message; ?>

    <form method="POST">
        <label>Current Password</label>
        <input type="password" name="old_password" required>

        <label>New Password</label>
        <input type="password" name="new_password" required>

        <label>Confirm New Password</label>
        <input type="password" name="confirm_password" required>

        <button type="submit" name="update_pass">Update Password</button>
    </form>
</div>

</body>
</html>