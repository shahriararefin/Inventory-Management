<?php
session_start();
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}
include 'db_connect.php';

$message = ""; // To store success/error messages

if(isset($_POST['submit'])){
    $name = mysqli_real_escape_string($conn, $_POST['item_name']);
    $cat = mysqli_real_escape_string($conn, $_POST['category']);
    $sku = mysqli_real_escape_string($conn, $_POST['sku']);
    $sup_id = (int)$_POST['supplier_id']; // Added supplier_id
    $buy_price = (float)$_POST['buy_price'];
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $is_fragile = isset($_POST['is_fragile']) ? 1 : 0;
    $admin = $_SESSION['admin'];
    
    $image_name = "default.png";

    // Create uploads folder if it doesn't exist
    if (!is_dir('uploads')) { mkdir('uploads', 0777, true); }

    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $ext = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
        $image_name = time() . "_" . $sku . "." . $ext; // Unique name using SKU
        move_uploaded_file($_FILES['product_image']['tmp_name'], "uploads/" . $image_name);
    }

    mysqli_begin_transaction($conn);

    try {
        // 1. Insert Product (Updated to include supplier_id)
        $sql = "INSERT INTO products (item_name, category, supplier_id, sku, buy_price, price, stock_quantity, is_fragile, image_path) 
                VALUES ('$name', '$cat', '$sup_id', '$sku', '$buy_price', '$price', '$stock', '$is_fragile', '$image_name')";
        
        if(!mysqli_query($conn, $sql)) { throw new Exception("Database Error: " . mysqli_error($conn)); }
        
        $new_product_id = mysqli_insert_id($conn);

        // 2. Log Initial Stock
        $log_sql = "INSERT INTO inventory_logs (product_id, type, quantity, reason, performed_by) 
                    VALUES ('$new_product_id', 'IN', '$stock', 'Initial Stock Entry', '$admin')";
        
        if(!mysqli_query($conn, $log_sql)) { throw new Exception("Error logging initial stock."); }

        mysqli_commit($conn);
        $message = "<p style='color:green; text-align:center; font-weight:bold;'>✅ Item & Audit Log saved successfully!</p>";

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $message = "<p style='color:red; text-align:center;'>❌ Error: " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Item - <?php echo SHOP_NAME; ?></title>
    <style>
        body { font-family: 'Segoe UI', Arial; padding: 20px; background-color: #f4f7f6; margin: 0; }
        .container { background: white; padding: 30px; border-radius: 10px; max-width: 500px; margin: 40px auto; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-top: 5px solid #d2a679; }
        input, select { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        label { font-size: 13px; font-weight: bold; color: #5d4037; display: block; margin-top: 10px; text-transform: uppercase; }
        button { background: #d2a679; color: white; border: none; padding: 15px; width: 100%; cursor: pointer; font-weight: bold; border-radius: 5px; margin-top: 20px; font-size: 16px; transition: 0.3s; }
        button:hover { background: #bc936a; }
        .back-link { display: inline-block; margin-bottom: 15px; text-decoration: none; color: #8b4513; font-weight: bold; }
    </style>
</head>
<body>
    <?php include 'navigation.php'; ?>

<div class="container">
    <a href="index.php" class="back-link">← Back to Dashboard</a>
    <h2 style="color: #5d4037; margin-top: 0;">Add New Product</h2>
    
    <?php echo $message; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <label>Item Name</label>
        <input type="text" name="item_name" required>
        
        <div style="display: flex; gap: 10px;">
            <div style="flex: 1;">
                <label>Category</label>
                <select name="category">
                    <option value="Home Decor">Home Decor</option>
                    <option value="Pottery">Pottery</option>
                    <option value="Gift Items">Gift Items</option>
                </select>
            </div>
            <div style="flex: 1;">
                <label>Supplier</label>
                <select name="supplier_id" required>
                    <option value="">Select Supplier</option>
                    <?php
                    $s_res = mysqli_query($conn, "SELECT id, supplier_name FROM suppliers");
                    while($s = mysqli_fetch_assoc($s_res)) {
                        echo "<option value='{$s['id']}'>{$s['supplier_name']}</option>";
                    }
                    ?>
                </select>
            </div>
        </div>
        
        <div style="display: flex; gap: 10px;">
            <div style="flex: 1;">
                <label>Buy Price (<?php echo CURRENCY; ?>)</label>
                <input type="number" name="buy_price" step="0.01" required>
            </div>
            <div style="flex: 1;">
                <label>Sell Price (<?php echo CURRENCY; ?>)</label>
                <input type="number" name="price" step="0.01" required>
            </div>
        </div>

        <div style="display: flex; gap: 10px;">
            <div style="flex: 1;">
                <label>SKU (Unique)</label>
                <input type="text" name="sku" placeholder="SG-101" required>
            </div>
            <div style="flex: 1;">
                <label>Initial Stock</label>
                <input type="number" name="stock" required>
            </div>
        </div>
        
        <label>Product Image</label>
        <input type="file" name="product_image" accept="image/*">
        
        <div style="margin-top: 15px;">
            <label style="font-weight: normal; cursor: pointer; text-transform: none;">
                <input type="checkbox" name="is_fragile" value="1" style="width: auto;"> This is a fragile item
            </label>
        </div>
        
        <button type="submit" name="submit">Create Product</button>
    </form>
</div>
</body>
</html>