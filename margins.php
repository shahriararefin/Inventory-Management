<?php
session_start();
if(!isset($_SESSION['admin'])){ header("Location: login.php"); exit(); }
include 'db_connect.php';

// Fetch products and calculate theoretical profit per unit
$sql = "SELECT item_name, sku, buy_price, price, category FROM products ORDER BY (price - buy_price) DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Margin Analysis - Shokher Ghor</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; padding: 30px; background-color: #f4f7f6; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #5d4037; color: white; }
        .markup-high { color: #2e7d32; font-weight: bold; }
        .markup-low { color: #c62828; font-weight: bold; }
        .card-grid { display: flex; gap: 20px; margin-bottom: 20px; }
        .card { background: #efebe9; padding: 15px; border-radius: 8px; flex: 1; text-align: center; }
    </style>
</head>
<body>
    <?php include 'navigation.php'; ?>

<div class="container">
    <a href="index.php" style="text-decoration:none; color:#5d4037; font-weight:bold;">← Back to Dashboard</a>
    
    <h1 style="color: #5d4037; margin-top: 20px;">Unit Margin Analysis</h1>
    <p>Compare your buying costs vs. selling prices to optimize your earnings.</p>

    <table>
        <tr>
            <th>Product</th>
            <th>Buy Price</th>
            <th>Sell Price</th>
            <th>Unit Profit</th>
            <th>Markup %</th>
        </tr>
        <?php 
        while($row = mysqli_fetch_assoc($result)) {
            $buy = $row['buy_price'];
            $sell = $row['price'];
            $profit = $sell - $buy;
            
            // Avoid division by zero
            $markup = ($buy > 0) ? ($profit / $buy) * 100 : 0;
            $markup_class = ($markup >= 40) ? 'markup-high' : ($markup < 15 ? 'markup-low' : '');

            echo "<tr>
                    <td><strong>{$row['item_name']}</strong><br><small>{$row['category']}</small></td>
                    <td>" . number_format($buy, 2) . "</td>
                    <td>" . number_format($sell, 2) . "</td>
                    <td style='font-weight:bold;'>+" . number_format($profit, 2) . " BDT</td>
                    <td class='$markup_class'>" . number_format($markup, 1) . "%</td>
                  </tr>";
        }
        ?>
    </table>
</div>

</body>
</html>