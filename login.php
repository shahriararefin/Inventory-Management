<?php
session_start();
include 'db_connect.php';

// If already logged in, skip the login page
if(isset($_SESSION['admin'])){
    header("Location: index.php");
    exit();
}

$error = "";

if(isset($_POST['login'])){
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $pass = $_POST['password'];

    // 1. Fetch the user from the database
    $query = "SELECT * FROM users WHERE username = '$user'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);

    // 2. Verify existence and password hash
    if ($row && password_verify($pass, $row['password'])) {
        // SUCCESS: Store critical info in the session
        $_SESSION['admin'] = $row['username'];
        $_SESSION['role'] = $row['role']; // Essential for hiding Audit Logs from staff
        
        header("Location: index.php");
        exit();
    } else {
        // FAIL: Provide a generic error for security
        $error = "Invalid Username or Password!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - <?php echo SHOP_NAME; ?></title>
    <style>
        body { font-family: 'Segoe UI', Arial; display: flex; justify-content: center; align-items: center; height: 100vh; background: #f2e6d9; margin: 0; }
        .login-card { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 320px; text-align: center; }
        .logo { color: #8b4513; font-size: 24px; font-weight: bold; margin-bottom: 20px; display: block; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; font-size: 16px; }
        button { width: 100%; padding: 12px; background: #8b4513; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 16px; transition: background 0.3s; }
        button:hover { background: #5d4037; }
        .error-msg { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 5px; font-size: 14px; margin-bottom: 15px; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div class="login-card">
        <span class="logo">🏺 <?php echo SHOP_NAME; ?></span>
        <h3 style="color: #666; margin-bottom: 25px;">Employee Login</h3>
        
        <?php if(!empty($error)): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="username" placeholder="Username" required autofocus>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Sign In</button>
        </form>
        
        <p style="font-size: 12px; color: #999; margin-top: 25px;">Secure POS System v2.0</p>
    </div>
</body>
</html>