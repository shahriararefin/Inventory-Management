<?php
session_start();
if(!isset($_SESSION['admin'])){ header("Location: login.php"); exit(); }
include 'db_connect.php';

// Base Query: Only get items that are low or out of stock
$sql = "SELECT * FROM products WHERE stock_quantity <= min_stock_level ORDER BY stock_quantity ASC";
$result = mysqli_query($conn, $sql);

// Calculate total cost to restock everything
$cost_query = mysqli_query($conn, "SELECT SUM((min_stock_level + 10 - stock_quantity) * buy_price) as total_cost 
                                   FROM products WHERE stock_quantity <= min_stock_level");
$total_investment = mysqli_fetch_assoc($cost_query)['total_cost'] ?? 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Restock Helper - Shokher Ghor</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; padding: 30px; background-color: #fdf5e6; }
        .container { max-width: 900px; margin: auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #8b4513; color: white; }
        .out-badge { background: #000; color: white; padding: 3px 8px; border-radius: 4px; font-size: 11px; }
        .low-badge { background: #ff0000; color: white; padding: 3px 8px; border-radius: 4px; font-size: 11px; }
        .no-print { margin-bottom: 20px; }
        @media print { .no-print { display: none; } body { background: white; padding: 0; } .container { box-shadow: none; width: 100%; } }
    </style>
</head>
<body>
    <?php include 'navigation.php'; ?>

<div class="container">
    <div class="no-print">
        <a href="index.php" style="text-decoration:none; color:#8b4513; font-weight:bold;">← Back to Dashboard</a>
        <button onclick="window.print()" style="float:right; padding: 8px 15px; cursor:pointer; background:#d2a679; border:none; color:white; border-radius:5px; font-weight:bold;">🖨️ Print Shopping List</button>
    </div>

    <h1>Restock Shopping List</h1>
    <p>The following items are currently below your safety stock levels.</p>

    <table>
        <tr>
            <th>Item Name</th>
            <th>Current Stock</th>
            <th>Min Level</th>
            <th>Suggested Buy</th>
            <th>Est. Cost</th>
        </tr>
        <?php 
        if(mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                $status = ($row['stock_quantity'] == 0) ? "<span class='out-badge'>OUT</span>" : "<span class='low-badge'>LOW</span>";
                
                // Logic: Buy enough to hit Min Level + 10 units buffer
                $suggested = ($row['min_stock_level'] - $row['stock_quantity']) + 10;
                $est_cost = $suggested * $row['buy_price'];

                echo "<tr>
                        <td><strong>{$row['item_name']}</strong> $status<br><small style='color:#888;'>SKU: {$row['sku']}</small></td>
                        <td>{$row['stock_quantity']}</td>
                        <td>{$row['min_stock_level']}</td>
                        <td style='color:#8b4513; font-weight:bold;'>+ $suggested units</td>
                        <td>" . number_format($est_cost, 2) . " BDT</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='5' style='text-align:center; padding: 40px;'>✅ All items are well-stocked! No restock needed.</td></tr>";
        }
        ?>
    </table>

    <?php if($total_investment > 0): ?>
    <div style="margin-top:30px; text-align:right; border-top: 2px solid #8b4513; padding-top:10px;">
        <h3 style="margin:0; color:#666;">Total Estimated Investment</h3>
        <p style="font-size:24px; font-weight:bold; color:#8b4513;"><?php echo number_format($total_investment, 2); ?> BDT</p>
    </div>
    <?php endif; ?>
</div>

</body>
</html>