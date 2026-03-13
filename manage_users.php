<?php
session_start();
include 'db_connect.php';

// PROTECTION: Only allow 'admin' role to access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("<h1 style='color:red; text-align:center; padding:50px;'>Access Denied! Only Super Admins can manage users.</h1>");
}

// Handle User Deletion
if(isset($_GET['delete'])){
    $id = (int)$_GET['delete'];
    
    // 1. Prevent an admin from deleting their own current session account
    // Note: Ensure $_SESSION['user_id'] is set during login
    if(isset($_SESSION['user_id']) && $id === (int)$_SESSION['user_id']) {
        header("Location: manage_users.php?msg=self_delete_error");
        exit();
    }

    // 2. Perform deletion (protecting other admins if necessary)
    $delete_query = "DELETE FROM users WHERE id = $id AND role != 'admin'";
    if(mysqli_query($conn, $delete_query)) {
        header("Location: manage_users.php?msg=deleted");
        exit();
    }
}

// Handle User Creation
if(isset($_POST['add_user'])){
    $new_u = mysqli_real_escape_string($conn, $_POST['username']);
    $new_p = password_hash($_POST['password'], PASSWORD_DEFAULT); 
    $new_r = $_POST['role'];

    $check = mysqli_query($conn, "SELECT * FROM users WHERE username = '$new_u'");
    if(mysqli_num_rows($check) > 0){
        $msg = "Error: Username already exists!";
    } else {
        $sql = "INSERT INTO users (username, password, role) VALUES ('$new_u', '$new_p', '$new_r')";
        if(mysqli_query($conn, $sql)){
            $msg = "✅ User '$new_u' created as '$new_r'.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users - <?php echo SHOP_NAME; ?></title>
    <style>
        body { font-family: 'Segoe UI', Arial; padding: 20px; background: #f4f7f6; }
        .card { background: white; padding: 25px; border-radius: 8px; max-width: 700px; margin: auto; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        input, select { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { background: #5d4037; color: white; border: none; padding: 12px; width: 100%; border-radius: 4px; cursor: pointer; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 30px; background: white; }
        th, td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
        th { background: #d2a679; color: white; font-size: 13px; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .badge-admin { background: #fee2e2; color: #991b1b; }
        .badge-staff { background: #e0f2fe; color: #075985; }
        .btn-del { color: #dc3545; text-decoration: none; font-size: 12px; font-weight: bold; }
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; font-weight: bold; text-align: center; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    </style>
</head>
<body>

<?php include 'navigation.php'; ?>

<div class="card">
    <h2>👤 User Management</h2>
    
    <?php 
    if(isset($msg)) echo "<div class='alert alert-success'>$msg</div>"; 
    if(isset($_GET['msg']) && $_GET['msg'] == 'self_delete_error') echo "<div class='alert alert-error'>❌ Error: You cannot delete your own account while logged in!</div>";
    if(isset($_GET['msg']) && $_GET['msg'] == 'deleted') echo "<div class='alert alert-success'>✅ User removed successfully.</div>";
    ?>

    <form method="POST">
        <div style="display: flex; gap: 10px;">
            <div style="flex: 2;">
                <label>Username</label>
                <input type="text" name="username" required placeholder="Full Name or ID">
            </div>
            <div style="flex: 2;">
                <label>Password</label>
                <input type="password" name="password" required placeholder="••••••••">
            </div>
            <div style="flex: 1;">
                <label>Role</label>
                <select name="role">
                    <option value="staff">Staff</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
        </div>
        <button type="submit" name="add_user">+ Create Account</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Access Level</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $res = mysqli_query($conn, "SELECT * FROM users");
            while($row = mysqli_fetch_assoc($res)){
                $role_class = ($row['role'] == 'admin') ? 'badge-admin' : 'badge-staff';
                echo "<tr>
                        <td>#{$row['id']}</td>
                        <td><strong>{$row['username']}</strong></td>
                        <td><span class='badge $role_class'>{$row['role']}</span></td>
                        <td>";
                
                // Logic: Don't show delete link if it's the logged-in user OR another admin
                if($row['role'] != 'admin' && (int)$row['id'] !== (int)$_SESSION['user_id']) {
                    echo "<a href='manage_users.php?delete={$row['id']}' class='btn-del' onclick=\"return confirm('Delete this user?');\">Remove</a>";
                } else if ((int)$row['id'] === (int)$_SESSION['user_id']) {
                    echo "<small style='color:#999;'>Current User</small>";
                } else {
                    echo "<small style='color:#ccc;'>Protected</small>";
                }
                echo "</td>
                      </tr>";
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>