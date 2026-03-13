<?php
session_start();
include 'db_connect.php';

// 1. Safety Check
if(!isset($_SESSION['admin']) || empty($_SESSION['cart'])) {
    header("Location: index.php");
    exit();
}

// 2. Catch the optional inputs from cart.php
$cust_name = mysqli_real_escape_string($conn, $_POST['cust_name'] ?? '');
$cust_phone = mysqli_real_escape_string($conn, $_POST['cust_phone'] ?? '');

$performed_by = $_SESSION['admin']; 

// 3. Generate a professional Order ID
$order_id = "ORD-" . date('ymd') . "-" . strtoupper(substr(md5(microtime()), 0, 4));

// START TRANSACTION
mysqli_begin_transaction($conn);

try {
    foreach($_SESSION['cart'] as $p_id => $item) {
        $qty = (int)$item['qty'];
        $p_id = (int)$p_id;

        // A. Re-verify stock and lock the row to prevent Race Conditions
        $stock_check = mysqli_query($conn, "SELECT stock_quantity, item_name FROM products WHERE id = $p_id FOR UPDATE");
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

        // C. Log the Sale with Customer Info
        // We use ONE consolidated query here
        $log_sql = "INSERT INTO inventory_logs (product_id, type, quantity, reason, order_id, performed_by, customer_name, customer_phone) 
                    VALUES ($p_id, 'OUT', $qty, 'Customer Sale', '$order_id', '$performed_by', '$cust_name', '$cust_phone')";
        
        if (!mysqli_query($conn, $log_sql)) {
            throw new Exception("Critical Error: Failed to create audit trail.");
        }
    }

    // IF EVERYTHING IS SUCCESSFUL: COMMIT
    mysqli_commit($conn);
    
    // Clear the cart
    unset($_SESSION['cart']);
    
    // Redirect to receipt
    header("Location: receipt.php?order_id=$order_id");
    exit();

} catch (Exception $e) {
    // IF ANYTHING FAILS: ROLLBACK
    mysqli_rollback($conn);
    
    $_SESSION['error'] = $e->getMessage();
    header("Location: cart.php");
    exit();
}
?>