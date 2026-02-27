<?php
session_start();
if(!isset($_SESSION['admin']) || $_SESSION['role'] !== 'admin'){ header("Location: login.php"); exit(); }
include 'db_connect.php';

// SQL to aggregate stock value per supplier
$sql = "SELECT 
            s.supplier_name, 
            s.contact_person, 
            COUNT(p.id) as total_products, 
            SUM(p.stock_quantity) as total_units,
            SUM(p.buy_price * p.stock_quantity) as total_investment
        FROM suppliers s
        LEFT JOIN products p ON s.id = p.supplier_id
        GROUP BY s.id
        ORDER BY total_investment DESC";

$report_res = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Supplier Report - <?php echo SHOP_NAME; ?></title>
    <style>
        body { font-family: 'Segoe UI', Arial; background: #f4f7f6; padding: 20px; }
        .report-card { background: white; padding: 30px; border-radius: 8px; max-width: 1000px; margin: auto; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .stat-grid { display: flex; gap: 20px; margin-bottom: 30px; }
        .stat-box { flex: 1; padding: 20px; border-radius: 8px; color: white; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 15px; border-bottom: 1px solid #eee; text-align: left; }
        th { background: #5d4037; color: white; text-transform: uppercase; font-size: 12px; }
        .progress-bar { background: #eee; border-radius: 10px; height: 10px; width: 100%; margin-top: 5px; }
        .progress-fill { background: #d2a679; height: 100%; border-radius: 10px; }
    </style>
</head>
<body>

<?php include 'navigation.php'; ?>

<div class="report-card">
    <h2 style="color: #5d4037; margin-top: 0;">📊 Supplier Investment Report</h2>
    <p style="color: #666; margin-bottom: 30px;">Summary of inventory value distributed by supplier.</p>

    <table>
        <thead>
            <tr>
                <th>Supplier Name</th>
                <th>Unique Items</th>
                <th>Total Units</th>
                <th>Investment Value</th>
                <th>Inventory Share</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $overall_total = 0;
            $data = [];
            while($row = mysqli_fetch_assoc($report_res)) {
                $overall_total += $row['total_investment'];
                $data[] = $row;
            }

            foreach($data as $item): 
                $percentage = ($overall_total > 0) ? ($item['total_investment'] / $overall_total) * 100 : 0;
            ?>
            <tr>
                <td>
                    <strong><?php echo htmlspecialchars($item['supplier_name']); ?></strong><br>
                    <small style="color: #999;"><?php echo htmlspecialchars($item['contact_person']); ?></small>
                </td>
                <td><?php echo $item['total_products']; ?></td>
                <td><?php echo (int)$item['total_units']; ?></td>
                <td><strong><?php echo number_format($item['total_investment'], 2); ?> <?php echo CURRENCY; ?></strong></td>
                <td width="200">
                    <div style="font-size: 11px; margin-bottom: 3px;"><?php echo round($percentage, 1); ?>%</div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $percentage; ?>%;"></div>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="background: #fafafa; font-weight: bold; font-size: 16px;">
                <td colspan="3" style="text-align: right;">Total Portfolio Value:</td>
                <td colspan="2"><?php echo number_format($overall_total, 2); ?> <?php echo CURRENCY; ?></td>
            </tr>
        </tfoot>
    </table>
</div>

</body>
</html>