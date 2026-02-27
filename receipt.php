<?php
session_start();
if(!isset($_SESSION['admin'])){ header("Location: login.php"); exit(); }
include 'db_connect.php';

// Sanitize the order_id
$order_id = isset($_GET['order_id']) ? mysqli_real_escape_string($conn, $_GET['order_id']) : '';

// Fetch items for this order
$sql = "SELECT l.*, p.item_name, p.price as unit_price 
        FROM inventory_logs l 
        JOIN products p ON l.product_id = p.id 
        WHERE l.order_id = '$order_id' AND l.type = 'OUT'";
$res = mysqli_query($conn, $sql);

if(mysqli_num_rows($res) == 0) { die("Order not found or empty."); }

$items = [];
$subtotal = 0;
while($row = mysqli_fetch_assoc($res)) {
    $items[] = $row;
    $subtotal += ($row['unit_price'] * $row['quantity']);
    $order_date = $row['date_added']; 
    $served_by = $row['performed_by']; // Use the actual person who made the sale
}

// Calculate VAT based on the subtotal
$tax_amount = ($subtotal * TAX_RATE) / 100;
$grand_total = $subtotal + $tax_amount;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order #<?php echo $order_id; ?> - <?php echo SHOP_NAME; ?></title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; background: #f4f4f4; padding: 20px; color: #333; }
        .receipt-box { background: white; width: 350px; margin: auto; padding: 25px; border-top: 5px solid #5d4037; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 20px; }
        .info { font-size: 13px; margin-bottom: 10px; border-bottom: 1px dashed #ccc; padding-bottom: 10px; line-height: 1.6; }
        .table { width: 100%; font-size: 14px; border-collapse: collapse; margin: 15px 0; }
        .table th { text-align: left; border-bottom: 2px solid #000; padding-bottom: 5px; }
        .table td { padding: 5px 0; }
        .total-section { border-top: 2px solid #000; padding-top: 10px; margin-top: 10px; }
        .total-row { display: flex; justify-content: space-between; margin-bottom: 5px; }
        .grand-total { font-weight: bold; font-size: 18px; margin-top: 5px; border-top: 1px solid #000; padding-top: 5px; }
        @media print { .no-print { display: none; } body { background: white; padding: 0; } .receipt-box { box-shadow: none; border: none; } }
    </style>
</head>
<body>

<div class="no-print" style="text-align:center; margin-bottom:20px;">
    <button onclick="window.location.href='index.php'" style="padding:10px; cursor:pointer;">← Dashboard</button>
    <button onclick="window.print()" style="padding:10px 20px; cursor:pointer; background:#5d4037; color:white; border:none; font-weight:bold;">🖨️ Print Receipt</button>
</div>

<div class="receipt-box">
    <div class="header">
        <h2 style="margin:0;"><?php echo SHOP_NAME; ?></h2>
        <small>Customer Invoice</small>
        <p style="margin: 5px 0;">#<?php echo $order_id; ?></p>
    </div>

    <div class="info">
        <strong>Date:</strong> <?php echo date('d-M-Y h:i A', strtotime($order_date)); ?><br>
        <strong>Admin:</strong> <?php echo htmlspecialchars($served_by); ?>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th style="text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($items as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                <td>x<?php echo $item['quantity']; ?></td>
                <td style="text-align: right;"><?php echo number_format($item['unit_price'] * $item['quantity'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="total-section">
        <div class="total-row">
            <span>Subtotal:</span>
            <span><?php echo number_format($subtotal, 2); ?></span>
        </div>
        <div class="total-row">
            <span>VAT (<?php echo TAX_RATE; ?>%):</span>
            <span><?php echo number_format($tax_amount, 2); ?></span>
        </div>
        <div class="total-row grand-total">
            <span>PAYABLE:</span>
            <span><?php echo number_format($grand_total, 2); ?> <?php echo CURRENCY; ?></span>
        </div>
    </div>

    <div class="header" style="margin-top:30px; font-size:12px;">
        <p>*** Thank You! ***</p>
        <p>Follow us for more updates!</p>
    </div>
</div>

</body>
</html>