<?php
session_start();
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}
include 'db_connect.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Item - <?php echo SHOP_NAME; ?></title>
    <style>
        body { font-family: Arial; padding: 20px; background-color: #f4f4f4; }
        .container { background: white; padding: 20px; border-radius: 8px; max-width: 400px; margin: auto; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        input, select { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        label { font-size: 14px; font-weight: bold; color: #555; display: block; margin-top: 10px; }
        button { background: #d2a679; color: white; border: none; padding: 12px; width: 100%; cursor: pointer; font-weight: bold; border-radius: 5px; margin-top: 10px; }
        button:hover { background: #bc936a; }
        .back-link { display: block; margin-bottom: 15px; text-decoration: none; color: #8b4513; font-size: 14px; }
    </style>
</head>
<body>
    <?php include 'navigation.php'; ?>

<div class="container">
    <a href="index.php" class="back-link">← Back to Dashboard</a>
    <h2>Add New Item</h2>
    
    <form action="" method="POST" enctype="multipart/form-data">
        <label>Item Name</label>
        <input type="text" name="item_name" placeholder="e.g., Clay Vase" required>
        
        <label>Category</label>
        <select name="category">
            <option value="Home Decor">Home Decor</option>
            <option value="Gift Items">Gift Items</option>
            <option value="Vases">Vases</option>
        </select>
        <label>Supplier</label>
<select name="supplier_id">
    <option value="">Select Supplier</option>
    <?php
    $s_res = mysqli_query($conn, "SELECT id, supplier_name FROM suppliers");
    while($s = mysqli_fetch_assoc($s_res)) {
        echo "<option value='{$s['id']}'>{$s['supplier_name']}</option>";
    }
    ?>
</select>
        
        <label>Buy Price (Cost <?php echo CURRENCY; ?>)</label>
        <input type="number" name="buy_price" step="0.01" placeholder="How much did you pay?" required>
        
        <label>Sell Price (<?php echo CURRENCY; ?>)</label>
        <input type="number" name="price" step="0.01" placeholder="Customer price" required>

        <label>SKU</label>
        <input type="text" name="sku" placeholder="SG-101" required>

        <label>Initial Stock</label>
        <input type="number" name="stock" placeholder="Quantity on hand" required>
        
        <label>Product Image</label>
        <input type="file" name="product_image" accept="image/*">
        
        <label style="font-weight: normal; cursor: pointer;">
            <input type="checkbox" name="is_fragile" value="1" style="width: auto;"> Mark as Fragile
        </label>
        
        <button type="submit" name="submit">Save to Inventory</button>
    </form>

    <?php
    if(isset($_POST['submit'])){
        $name = mysqli_real_escape_string($conn, $_POST['item_name']);
        $cat = mysqli_real_escape_string($conn, $_POST['category']);
        $sku = mysqli_real_escape_string($conn, $_POST['sku']);
        $buy_price = (float)$_POST['buy_price'];
        $price = (float)$_POST['price'];
        $stock = (int)$_POST['stock'];
        $is_fragile = isset($_POST['is_fragile']) ? 1 : 0;
        $admin = $_SESSION['admin'];
        
        $image_name = "default.png";

        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
            $image_name = time() . "_" . basename($_FILES['product_image']['name']);
            move_uploaded_file($_FILES['product_image']['tmp_name'], "uploads/" . $image_name);
        }

        // --- START TRANSACTION ---
        mysqli_begin_transaction($conn);

        try {
            // 1. Insert Product
            $sql = "INSERT INTO products (item_name, category, sku, buy_price, price, stock_quantity, is_fragile, image_path) 
                    VALUES ('$name', '$cat', '$sku', '$buy_price', '$price', '$stock', '$is_fragile', '$image_name')";
            
            if(!mysqli_query($conn, $sql)) { throw new Exception("Error creating product."); }
            
            $new_product_id = mysqli_insert_id($conn);

            // 2. Log Initial Stock in Audit Trail
            $log_sql = "INSERT INTO inventory_logs (product_id, type, quantity, reason, performed_by) 
                        VALUES ('$new_product_id', 'IN', '$stock', 'Initial Stock Entry', '$admin')";
            
            if(!mysqli_query($conn, $log_sql)) { throw new Exception("Error logging initial stock."); }

            mysqli_commit($conn);
            echo "<p style='color:green; text-align:center; font-weight:bold; margin-top:15px;'>✅ Item & Audit Log saved successfully!</p>";

        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo "<p style='color:red; text-align:center;'>❌ Error: " . $e->getMessage() . "</p>";
        }
    }
    ?>
</div>

</body>
</html>