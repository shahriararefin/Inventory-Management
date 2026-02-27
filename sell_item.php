<?php
session_start();
// Security: Check if logged in
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}
include 'db_connect.php';

// 1. Fetch Today's Totals for the pulse bar
$today = date('Y-m-d');
$stats_query = mysqli_query($conn, "SELECT 
    SUM(p.price * l.quantity) as day_revenue, 
    SUM(l.quantity) as day_units 
    FROM inventory_logs l 
    JOIN products p ON l.product_id = p.id 
    WHERE l.type = 'OUT' AND DATE(l.date_added) = '$today'");
$stats = mysqli_fetch_assoc($stats_query);

// 2. Catch the ID from the URL
$selected_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch the specific product details for display
$product = null;
if ($selected_id > 0) {
    $res = mysqli_query($conn, "SELECT * FROM products WHERE id = $selected_id");
    $product = mysqli_fetch_assoc($res);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Record Sale - Shokher Ghor</title>
    <style>
        body { font-family: 'Segoe UI', Arial; background-color: #fdf5e6; padding: 20px; }
        .daily-bar { 
            background: #8b4513; color: white; padding: 15px; border-radius: 8px; 
            max-width: 500px; margin: 0 auto 20px auto; display: flex; justify-content: space-around; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .sell-card { background: white; padding: 30px; border-radius: 8px; max-width: 500px; margin: auto; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .product-info { background: #f9f9f9; padding: 15px; border-left: 5px solid #d2a679; margin-bottom: 20px; }
        input, select { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn-sell { background: #8b4513; color: white; border: none; padding: 15px; width: 100%; cursor: pointer; font-size: 16px; font-weight: bold; border-radius: 4px; }
        .btn-sell:hover { background: #a0522d; }
        .total-preview { background: #eee; padding: 10px; margin-top: 10px; font-weight: bold; text-align: right; }
        .stat-item { text-align: center; flex: 1; }
    </style>
</head>
<body>

<?php include 'navigation.php'; ?>

<div class="daily-bar">
    <div class="stat-item">
        <small style="text-transform:uppercase; font-size:10px; opacity:0.8;">Today's Revenue</small>
        <div style="font-size:18px; font-weight:bold;"><?php echo number_format($stats['day_revenue'] ?? 0, 2); ?> BDT</div>
    </div>
    <div style="border-left: 1px solid rgba(255,255,255,0.3);"></div>
    <div class="stat-item">
        <small style="text-transform:uppercase; font-size:10px; opacity:0.8;">Units Sold</small>
        <div style="font-size:18px; font-weight:bold;"><?php echo (int)($stats['day_units'] ?? 0); ?></div>
    </div>
</div>

<div class="sell-card">
    <a href="index.php" style="text-decoration:none; color:#8b4513; font-size: 14px;">← Back to Inventory</a>
    <h2 style="color: #8b4513; margin-top: 10px;">Process Sale</h2>

    <?php if($product): ?>
        <div class="product-info">
            <strong>Item:</strong> <?php echo $product['item_name']; ?><br>
            <strong>Current Stock:</strong> <?php echo $product['stock_quantity']; ?><br>
            <strong>Unit Price:</strong> <?php echo number_format($product['price'], 2); ?> BDT
        </div>

        <form action="" method="POST">
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
            
            <label>Quantity to Sell:</label>
            <input type="number" name="qty" id="qty" min="1" max="<?php echo $product['stock_quantity']; ?>" value="1" required oninput="calculateTotal()">

            <label>Discount (BDT):</label>
            <input type="number" name="discount" id="discount" min="0" value="0" step="0.01" oninput="calculateTotal()">

            <label>Notes (Optional):</label>
            <input type="text" name="reason" placeholder="e.g. Regular customer discount">

            <div class="total-preview">
                Final Total: <span id="final_price"><?php echo number_format($product['price'], 2); ?></span> BDT
            </div>
            <br>
            <button type="submit" name="confirm_sale" class="btn-sell">Complete & Print Receipt</button>
        </form>

        <script>
            function calculateTotal() {
                const price = <?php echo $product['price']; ?>;
                const qty = document.getElementById('qty').value || 0;
                const discount = document.getElementById('discount').value || 0;
                const final = (price * qty) - discount;
                document.getElementById('final_price').innerText = final.toFixed(2);
            }
        </script>

    <?php else: ?>
        <p style="color:red; text-align:center; padding: 20px;">No product selected. Please select an item from the dashboard.</p>
    <?php endif; ?>
    
    <?php
    if(isset($_POST['confirm_sale'])){
        $p_id = (int)$_POST['product_id'];
        $qty = (int)$_POST['qty'];
        $discount = (float)$_POST['discount'];
        $reason = mysqli_real_escape_string($conn, $_POST['reason']);

        mysqli_begin_transaction($conn);

        try {
            $check = mysqli_query($conn, "SELECT stock_quantity FROM products WHERE id = $p_id");
            $row = mysqli_fetch_assoc($check);

            if($row['stock_quantity'] < $qty) {
                throw new Exception("Insufficient stock!");
            }

            mysqli_query($conn, "UPDATE products SET stock_quantity = stock_quantity - $qty WHERE id = $p_id");
            
            $log_reason = "Sale: $reason (Discount: $discount BDT)";
            mysqli_query($conn, "INSERT INTO inventory_logs (product_id, type, quantity, reason) 
                                VALUES ('$p_id', 'OUT', '$qty', '$log_reason')");

            $new_log_id = mysqli_insert_id($conn);
            mysqli_commit($conn);

            echo "<p style='color:green; font-weight:bold; text-align:center;'>Success! Processing receipt...</p>";
            echo "<script>setTimeout(()=> { window.location.href='receipt.php?log_id=$new_log_id'; }, 800);</script>";

        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo "<p style='color:red; text-align:center;'>Error: " . $e->getMessage() . "</p>";
        }
    }
    ?>
</div>

</body>
</html>