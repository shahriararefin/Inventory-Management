<?php
session_start();
// 1. Critical Security Gate: Only admins can view audit trails
if(!isset($_SESSION['admin']) || $_SESSION['role'] !== 'admin'){ 
    die("<h1 style='color:red; text-align:center; padding:50px;'>Access Denied! Only Super Admins can view audit logs.</h1>");
}
include 'db_connect.php';

// Handle Filter by Admin (Optional Search)
$admin_filter = mysqli_real_escape_string($conn, $_GET['admin_search'] ?? '');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Audit Logs - <?php echo SHOP_NAME; ?></title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f7f6; padding: 20px; }
        .log-card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin: auto; max-width: 1100px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border-bottom: 1px solid #eee; padding: 15px; text-align: left; }
        th { background: #5d4037; color: white; text-transform: uppercase; font-size: 13px; letter-spacing: 1px; }
        tr:hover { background: #fcf9f5; }
        .type-in { color: #059669; font-weight: bold; background: #ecfdf5; padding: 4px 8px; border-radius: 4px; }
        .type-out { color: #dc2626; font-weight: bold; background: #fef2f2; padding: 4px 8px; border-radius: 4px; }
        .badge-user { background: #e2e8f0; color: #1e293b; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .search-box { padding: 10px; width: 250px; border: 1px solid #ddd; border-radius: 4px; }
        .btn-filter { padding: 10px 20px; cursor:pointer; background:#d2a679; color:#5d4037; border:none; border-radius:4px; font-weight:bold; transition: 0.3s; }
        .btn-filter:hover { background:#bc936a; }
    </style>
</head>
<body>

<?php include 'navigation.php'; ?>

<div class="log-card">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f4f7f6; padding-bottom: 15px; margin-bottom: 15px;">
        <h2 style="color: #5d4037; margin: 0;">📜 System Audit Trail</h2>
        <form method="GET" style="display: flex; gap: 10px;">
            <input type="text" name="admin_search" class="search-box" placeholder="Filter by user..." value="<?php echo htmlspecialchars($admin_filter); ?>">
            <button type="submit" class="btn-filter">Filter Logs</button>
            <?php if($admin_filter): ?> 
                <a href="audit_logs.php" style="align-self: center; font-size: 13px; color: #666;">Clear</a> 
            <?php endif; ?>
        </form>
    </div>

    <div style="background: #fff8e1; border-left: 5px solid #ffc107; padding: 10px 20px; margin-bottom: 25px;">
        <p style="color: #856404; font-size: 13px; margin: 0;">
            <strong>Security Notice:</strong> This log is an immutable record. Every stock adjustment, sale, and restock is tracked with a timestamp and the user responsible.
        </p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Timestamp</th>
                <th>User</th>
                <th>Movement</th>
                <th>Item Description</th>
                <th>Qty</th>
                <th>Reference/Reason</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // We use a JOIN to get the item_name, but we still display logs even if a product was deleted (LEFT JOIN)
            $query = "SELECT l.*, p.item_name 
                      FROM inventory_logs l 
                      LEFT JOIN products p ON l.product_id = p.id ";
            
            if ($admin_filter) {
                $query .= " WHERE l.performed_by LIKE '%$admin_filter%' ";
            }

            $query .= " ORDER BY l.date_added DESC";
            
            $res = mysqli_query($conn, $query);

            if(mysqli_num_rows($res) > 0) {
                while($row = mysqli_fetch_assoc($res)) {
                    $type_label = ($row['type'] == 'IN') ? 'INBOUND' : 'OUTBOUND';
                    $type_class = ($row['type'] == 'IN') ? 'type-in' : 'type-out';
                    $formatted_date = date('d M Y | h:i A', strtotime($row['date_added']));
                    $item_display = $row['item_name'] ?? "<span style='color:red;'>Deleted Product (ID: {$row['product_id']})</span>";
                    
                    echo "<tr>
                        <td style='font-size: 13px; color: #64748b;'>$formatted_date</td>
                        <td><span class='badge-user'>" . htmlspecialchars($row['performed_by'] ?? 'System') . "</span></td>
                        <td><span class='$type_class'>$type_label</span></td>
                        <td><strong>" . htmlspecialchars($item_display) . "</strong></td>
                        <td><strong>{$row['quantity']}</strong></td>
                        <td style='font-size: 13px; color: #666;'>" . 
                            ($row['order_id'] ? "Order Ref: <strong>" . $row['order_id'] . "</strong>" : htmlspecialchars($row['reason'])) . 
                        "</td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='6' style='text-align:center; padding: 50px; color: #999;'>No history found. Try adjusting your filters.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>