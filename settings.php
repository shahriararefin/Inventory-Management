<?php
session_start();
// 1. Protection: Only admins can change system-wide settings
if(!isset($_SESSION['admin']) || $_SESSION['role'] !== 'admin'){ 
    header("Location: login.php"); 
    exit(); 
}
include 'db_connect.php';

// 2. Handle Save Settings
if(isset($_POST['save_settings'])){
    $name = mysqli_real_escape_string($conn, $_POST['shop_name']);
    $curr = mysqli_real_escape_string($conn, $_POST['currency']);
    $addr = mysqli_real_escape_string($conn, $_POST['address']);
    $tax = (float)$_POST['tax_rate'];
    
    // Updated query to include tax_rate
    $update = "UPDATE shop_settings SET 
                shop_name='$name', 
                currency='$curr', 
                address='$addr', 
                tax_rate='$tax' 
               WHERE id=1";

    if(mysqli_query($conn, $update)){
        header("Location: settings.php?msg=saved");
        exit();
    }
}

// 3. Fetch current settings AFTER any updates
$res = mysqli_query($conn, "SELECT * FROM shop_settings WHERE id = 1");
$settings = mysqli_fetch_assoc($res);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Settings - <?php echo SHOP_NAME; ?></title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f7f6; padding: 20px; }
        .settings-card { background: white; padding: 30px; border-radius: 8px; max-width: 550px; margin: auto; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-top: 5px solid #5d4037; }
        h2 { color: #5d4037; margin-top: 0; }
        label { display: block; margin-top: 15px; font-weight: bold; font-size: 14px; color: #555; }
        input, textarea { width: 100%; padding: 12px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; font-size: 15px; }
        .btn-save { background: #5d4037; color: white; border: none; padding: 15px; width: 100%; cursor: pointer; font-weight: bold; border-radius: 4px; margin-top: 25px; transition: 0.3s; }
        .btn-save:hover { background: #3e2723; }
        .maintenance-box { background: #fff; padding: 25px; border-radius: 8px; border: 1px solid #e2e8f0; max-width: 550px; margin: 30px auto; }
    </style>
</head>
<body>

<?php include 'navigation.php'; ?>

<div class="settings-card">
    <h2>⚙️ Shop Configuration</h2>
    <p style="color: #666; font-size: 13px; margin-bottom: 20px;">Adjust global parameters for Shokher Ghor.</p>
    
    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'saved'): ?>
        <p style="background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; font-weight: bold;">✅ Settings updated successfully!</p>
    <?php endif; ?>

    <form method="POST">
        <label>Shop Name:</label>
        <input type="text" name="shop_name" value="<?php echo htmlspecialchars($settings['shop_name']); ?>" required>

        <div style="display: flex; gap: 15px;">
            <div style="flex: 1;">
                <label>Currency Symbol:</label>
                <input type="text" name="currency" value="<?php echo htmlspecialchars($settings['currency']); ?>" required placeholder="BDT">
            </div>
            <div style="flex: 1;">
                <label>VAT / Tax (%):</label>
                <input type="number" name="tax_rate" step="0.01" value="<?php echo $settings['tax_rate']; ?>" required>
            </div>
        </div>

        <label>Shop Address:</label>
        <textarea name="address" rows="3"><?php echo htmlspecialchars($settings['address']); ?></textarea>

        <button type="submit" name="save_settings" class="btn-save">Save All Changes</button>
    </form>
</div>

<div class="maintenance-box">
    <h3 style="color: #8b4513; margin-top: 0;">🛠️ System Maintenance</h3>
    <p style="font-size: 13px; color: #64748b; margin-bottom: 20px;">
        Generate a full SQL snapshot of your products, sales, and users. Essential for migration to your office PC.
    </p>
    
    <a href="backup_db.php" style="display: inline-block; background: #0f172a; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 14px; transition: 0.3s;">
        📦 Download .SQL Backup
    </a>
</div>

</body>
</html>