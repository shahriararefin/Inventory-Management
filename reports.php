<?php
session_start();
// 1. Session Protection
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}
include 'db_connect.php';

// 2. Capture Filters
$start = $_GET['start_date'] ?? '';
$end = $_GET['end_date'] ?? '';
$search_sale = mysqli_real_escape_string($conn, $_GET['search_sale'] ?? '');
$search_id = isset($_GET['search_id']) ? (int)$_GET['search_id'] : '';

// 3. Build Dynamic Condition
$date_condition = " l.type = 'OUT' ";

if(!empty($search_id)) {
    // If searching by ID, we look for that specific log entry
    $date_condition .= " AND l.id = $search_id ";
} elseif(!empty($start) && !empty($end)) {
    $date_condition .= " AND l.date_added BETWEEN '$start 00:00:00' AND '$end 23:59:59' ";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo SHOP_NAME; ?> - Business Reports</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 30px; background-color: #f9f9f9; }
        .stat-card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); display: inline-block; min-width: 200px; margin-right: 20px; vertical-align: top; border-top: 5px solid #d2a679; }
        .stat-card h3 { margin: 0; color: #5d4037; font-size: 14px; text-transform: uppercase; }
        .stat-card p { font-size: 24px; font-weight: bold; margin: 10px 0 0 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 30px; background: white; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background-color: #5d4037; color: white; }
        .btn-back { text-decoration: none; color: #5d4037; font-weight: bold; margin-bottom: 20px; display: inline-block; }
        .print-btn { text-decoration: none; font-size: 18px; color: #5d4037; }
        .filter-container { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .profit-text { color: #28a745; font-weight: bold; }
        .search-divider { border-left: 1px solid #ddd; height: 40px; margin: 0 10px; }
    </style>
</head>
<body>
    <?php include 'navigation.php'; ?>

<h1 style="color: #5d4037;"><?php echo SHOP_NAME; ?>: Sales Performance</h1>
<a href="index.php" class="btn-back">← Back to Inventory</a>

<div style="margin-top: 20px; margin-bottom: 40px;">
    <?php
    // 4. Calculate Filtered Revenue AND Profit
    $finance_query = mysqli_query($conn, "SELECT 
        SUM(p.price * l.quantity) as total_revenue,
        SUM((p.price - p.buy_price) * l.quantity) as total_profit 
        FROM inventory_logs l 
        JOIN products p ON l.product_id = p.id 
        WHERE $date_condition");
    
    $finance_data = mysqli_fetch_assoc($finance_query);
    $revenue = $finance_data['total_revenue'] ?? 0;
    $profit = $finance_data['total_profit'] ?? 0;

    $count_query = mysqli_query($conn, "SELECT SUM(l.quantity) as total_qty FROM inventory_logs l WHERE $date_condition");
    $items_sold = mysqli_fetch_assoc($count_query)['total_qty'] ?? 0;
    ?>

    <div class="stat-card">
        <h3>Total Revenue</h3>
        <p><?php echo number_format($revenue, 2) . " " . CURRENCY; ?></p>
    </div>

    <div class="stat-card" style="border-top-color: #28a745;">
        <h3>Estimated Profit</h3>
        <p class="profit-text"><?php echo number_format($profit, 2) . " " . CURRENCY; ?></p>
    </div>

    <div class="stat-card">
        <h3>Total Units Sold</h3>
        <p><?php echo (int)$items_sold; ?> units</p>
    </div>
</div>

<div class="filter-container">
    <form method="GET">
        <div style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
            <div>
                <label style="display:block; font-size: 11px; color: #666; margin-bottom:5px;">Invoice Search (#)</label>
                <input type="number" name="search_id" value="<?php echo $search_id; ?>" placeholder="Log ID" style="padding: 10px; width: 80px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            
            <div class="search-divider"></div>

            <div>
                <label style="display:block; font-size: 11px; color: #666; margin-bottom:5px;">Start Date</label>
                <input type="date" name="start_date" value="<?php echo htmlspecialchars($start); ?>" style="padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            <div>
                <label style="display:block; font-size: 11px; color: #666; margin-bottom:5px;">End Date</label>
                <input type="date" name="end_date" value="<?php echo htmlspecialchars($end); ?>" style="padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            <div>
                <label style="display:block; font-size: 11px; color: #666; margin-bottom:5px;">Product Name</label>
                <input type="text" name="search_sale" placeholder="Filter item..." value="<?php echo htmlspecialchars($_GET['search_sale'] ?? ''); ?>" style="padding: 10px; width: 150px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            
            <button type="submit" style="padding: 10px 20px; background: #5d4037; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">Apply Filters</button>
            <a href="reports.php" style="padding: 10px; color: #666; text-decoration: none; font-size: 14px;">Reset</a>

            <a href="export_sales.php?start_date=<?php echo $start; ?>&end_date=<?php echo $end; ?>" 
               style="background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; font-weight: bold;">
               📥 Export CSV
            </a>
        </div>
    </form>
</div>

<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Product Details</th>
            <th>Qty</th>
            <th>Unit Price</th>
            <th>Profit Margin</th>
            <th>Subtotal</th>
            <th>Receipt</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $history_sql = "SELECT l.id, l.order_id, p.item_name, p.price, p.buy_price, l.quantity, l.date_added 
                    FROM inventory_logs l 
                    JOIN products p ON l.product_id = p.id 
                    WHERE $date_condition";
    
    if(!empty($search_sale)) {
        $history_sql .= " AND p.item_name LIKE '%$search_sale%'";
    }
    
    $history_sql .= " ORDER BY l.date_added DESC";
    $history_result = mysqli_query($conn, $history_sql);

    if(mysqli_num_rows($history_result) > 0) {
        while($row = mysqli_fetch_assoc($history_result)) {
            $unit_profit = $row['price'] - $row['buy_price'];
            $total_row_profit = $unit_profit * $row['quantity'];
            $subtotal = $row['price'] * $row['quantity'];

            echo "<tr>
                    <td style='font-size: 13px; color: #666;'>" . date('M d, Y h:i A', strtotime($row['date_added'])) . "</td>
                    <td><strong>{$row['item_name']}</strong><br><small>ID: #{$row['id']} | Ref: {$row['order_id']}</small></td>
                    <td>{$row['quantity']}</td>
                    <td>" . number_format($row['price'], 2) . "</td>
                    <td class='profit-text'>+" . number_format($total_row_profit, 2) . "</td>
                    <td><strong>" . number_format($subtotal, 2) . " " . CURRENCY . "</strong></td>
                    <td><a href='receipt.php?order_id={$row['order_id']}' target='_blank' class='print-btn' title='View Receipt'>📄</a></td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='7' style='text-align:center; padding: 30px;'>No records found for the selected criteria.</td></tr>";
    }
    ?>
    </tbody>
</table>

<?php if($_SESSION['role'] === 'admin'): ?>
<div style="background: #fff0f0; border: 1px solid #ffcccc; padding: 25px; border-radius: 8px; margin-top: 50px;">
    <h3 style="color: #c00; margin-top: 0;">⚠️ Danger Zone: Bulk Delete Logs</h3>
    <p style="font-size: 13px; color: #666;">Use this to clean up the database. This action cannot be undone.</p>
    <form action="bulk_delete_logs.php" method="POST" onsubmit="return confirm('WARNING: This will permanently erase sales history! Proceed?');">
        <select name="delete_type" style="padding: 10px; border-radius: 4px; border: 1px solid #ddd; margin-right: 10px;">
            <option value="old">Delete logs older than 30 days</option>
            <option value="all">Delete ALL logs (Reset to zero)</option>
        </select>
        <button type="submit" name="bulk_delete" style="padding: 10px 20px; background: #c00; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
            Execute Purge
        </button>
    </form>
</div>
<?php endif; ?>

</body>
</html>