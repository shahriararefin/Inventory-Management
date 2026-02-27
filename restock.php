<?php
session_start();
if(!isset($_SESSION['admin'])){ header("Location: login.php"); exit(); }
include 'db_connect.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$res = mysqli_query($conn, "SELECT * FROM products WHERE id = $id");
$product = mysqli_fetch_assoc($res);

if(!$product) { die("Product not found."); }

if(isset($_POST['restock'])){
    $qty = (int)$_POST['qty'];
    $admin_user = $_SESSION['admin']; // Capture the current user
    
    // START TRANSACTION
    mysqli_begin_transaction($conn);

    try {
        // 1. Update Product Stock
        $update_query = "UPDATE products SET stock_quantity = stock_quantity + $qty WHERE id = $id";
        if(!mysqli_query($conn, $update_query)) {
            throw new Exception("Failed to update product quantity.");
        }
        
        // 2. Log the Arrival with 'performed_by'
        // This ensures the action is permanently linked to the admin
        $log_sql = "INSERT INTO inventory_logs (product_id, type, quantity, reason, performed_by) 
                    VALUES ('$id', 'IN', '$qty', 'Restock: New Shipment Received', '$admin_user')";

        if(!mysqli_query($conn, $log_sql)) {
            throw new Exception("Failed to record the audit log.");
        }

        // COMMIT changes if both successful
        mysqli_commit($conn);
        header("Location: index.php?msg=restocked");
        exit();

    } catch (Exception $e) {
        // ROLLBACK if anything goes wrong to maintain data integrity
        mysqli_rollback($conn);
        $error = "Transaction failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Restock - Shokher Ghor</title>
    <style>
        body { font-family: 'Segoe UI', Arial; background: #fdf5e6; padding: 50px; }
        .restock-card { background: white; padding: 30px; border-radius: 10px; max-width: 400px; margin: auto; box-shadow: 0 4px 10px rgba(0,0,0,0.1); border-top: 5px solid #28a745; }
        .product-meta { background: #e9ecef; padding: 10px; border-radius: 5px; margin: 15px 0; font-size: 14px; }
        input { width: 100%; padding: 12px; margin: 15px 0; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; font-size: 16px; }
        button { background: #28a745; color: white; border: none; padding: 12px; width: 100%; border-radius: 5px; cursor: pointer; font-weight: bold; }
        button:hover { background: #218838; }
        .back-link { text-decoration: none; color: #666; font-size: 13px; }
    </style>
</head>
<body>

<div class="restock-card">
    <a href="index.php" class="back-link">← Cancel</a>
    <h2 style="color: #28a745;">Restock Item</h2>
    
    <div class="product-meta">
        <strong>Item:</strong> <?php echo htmlspecialchars($product['item_name']); ?><br>
        <strong>SKU:</strong> <?php echo htmlspecialchars($product['sku']); ?><br>
        <strong>Current Stock:</strong> <?php echo $product['stock_quantity']; ?>
    </div>

    <form method="POST">
        <label>Quantity Received:</label>
        <input type="number" name="qty" placeholder="Enter amount..." required min="1">
        <button type="submit" name="restock">Add to Stock</button>
    </form>
    <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
</div>

</body>
</html>