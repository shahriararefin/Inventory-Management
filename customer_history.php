<?php
session_start();
if(!isset($_SESSION['admin'])){ header("Location: login.php"); exit(); }
include 'db_connect.php';

$phone_search = mysqli_real_escape_string($conn, $_GET['phone'] ?? '');

$results = null;
if ($phone_search) {
    // We join logs with products to get the names of the items they bought
    $query = "SELECT l.*, p.item_name 
              FROM inventory_logs l 
              JOIN products p ON l.product_id = p.id 
              WHERE l.customer_phone = '$phone_search' 
              ORDER BY l.date_added DESC";
    $results = mysqli_query($conn, $query);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customer History - <?php echo SHOP_NAME; ?></title>
    <style>
        body { font-family: 'Segoe UI', Arial; background: #f4f7f6; padding: 20px; }
        .container { background: white; padding: 30px; border-radius: 8px; max-width: 900px; margin: auto; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .search-box { display: flex; gap: 10px; margin-bottom: 30px; }
        input { flex: 1; padding: 12px; border: 1px solid #ddd; border-radius: 4px; }
        button { padding: 12px 25px; background: #5d4037; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px; border-bottom: 1px solid #eee; text-align: left; }
        th { background: #d2a679; color: white; }
        .receipt-link { color: #8b4513; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

<?php include 'navigation.php'; ?>

<div class="container">
    <h2>🔍 Customer Purchase History</h2>
    <p style="color: #666; font-size: 14px; margin-bottom: 20px;">Search by phone number to see previous orders and preferences.</p>

    <form method="GET" class="search-box">
        <input type="text" name="phone" placeholder="Enter Customer Phone Number..." value="<?php echo htmlspecialchars($phone_search); ?>" required>
        <button type="submit">Search History</button>
    </form>

    <?php if ($phone_search): ?>
        <?php if ($results && mysqli_num_rows($results) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Order ID</th>
                        <th>Item Bought</th>
                        <th>Qty</th>
                        <th>Sold By</th>
                        <th>View</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($results)): ?>
                    <tr>
                        <td><?php echo date('d M Y', strtotime($row['date_added'])); ?></td>
                        <td><code><?php echo $row['order_id']; ?></code></td>
                        <td><strong><?php echo htmlspecialchars($row['item_name']); ?></strong></td>
                        <td><?php echo $row['quantity']; ?></td>
                        <td><?php echo htmlspecialchars($row['performed_by']); ?></td>
                        <td><a href="receipt.php?order_id=<?php echo $row['order_id']; ?>" class="receipt-link">Receipt</a></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="text-align: center; padding: 40px; color: #999;">
                No purchase history found for this phone number.
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

</body>
</html>