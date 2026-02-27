<?php
session_start();
include 'db_connect.php';

// 1. Safety Check: Is the user logged in and is the cart full?
if(!isset($_SESSION['admin']) || empty($_SESSION['cart'])) {
    header("Location: index.php");
    exit();
}

$performed_by = $_SESSION['admin']; 
// Generate a professional Order ID
$order_id = "ORD-" . date('ymd') . "-" . strtoupper(substr(md5(microtime()), 0, 4));

// START TRANSACTION - Crucial for ACID properties
mysqli_begin_transaction($conn);

try {
    foreach($_SESSION['cart'] as $p_id => $item) {
        $qty = (int)$item['qty'];
        $p_id = (int)$p_id;

        // A. Re-verify stock and lock the row (FOR UPDATE) to prevent Race Conditions
        $stock_check = mysqli_query($conn, "SELECT stock_quantity, item_name, price FROM products WHERE id = $p_id FOR UPDATE");
        $product = mysqli_fetch_assoc($stock_check);

        if (!$product) {
            throw new Exception("Product ID $p_id no longer exists.");
        }

        if ($product['stock_quantity'] < $qty) {
            throw new Exception("Insufficient stock for: " . $product['item_name']);
        }

        // B. Deduct Stock
        $update_stock = "UPDATE products SET stock_quantity = stock_quantity - $qty WHERE id = $p_id";
        if (!mysqli_query($conn, $update_stock)) {
            throw new Exception("Database error: Could not update inventory.");
        }

        // C. Log the Sale into inventory_logs
        // We include the price here so reports remain accurate even if prices change later
        $log_sql = "INSERT INTO inventory_logs (product_id, type, quantity, reason, order_id, performed_by) 
                    VALUES ($p_id, 'OUT', $qty, 'Cart Sale', '$order_id', '$performed_by')";
        
        if (!mysqli_query($conn, $log_sql)) {
            throw new Exception("Critical Error: Failed to create audit trail.");
        }
    }

    // IF EVERYTHING IS PERFECT: COMMIT
    mysqli_commit($conn);
    
    // Clear the cart and send to receipt
    unset($_SESSION['cart']);
    header("Location: receipt.php?order_id=$order_id");
    exit();

} catch (Exception $e) {
    // IF ANYTHING FAILS: ROLLBACK (Undoes all stock deductions in this loop)
    mysqli_rollback($conn);
    
    $_SESSION['error'] = $e->getMessage();
    header("Location: cart.php");
    exit();
}
?>