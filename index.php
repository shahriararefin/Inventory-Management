<?php
session_start();

// 1. Protect the page - Redirect if not logged in
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}

include 'db_connect.php';

// 2. Handle Search or Filtering
$search = mysqli_real_escape_string($conn, $_GET['search'] ?? '');
$filter = $_GET['filter'] ?? '';

// Build the main product query
$query = "SELECT * FROM products WHERE (item_name LIKE '%$search%' OR sku LIKE '%$search%')";
if ($filter == 'low_stock') {
    $query .= " AND stock_quantity <= min_stock_level";
}
$result = mysqli_query($conn, $query);

// 3. Optimized Statistics: Fetch all counts and sums in one single database hit
$stats_res = mysqli_query($conn, "SELECT 
    COUNT(*) as total_unique,
    SUM(stock_quantity) as total_units,
    SUM(price * stock_quantity) as total_val,
    SUM(CASE WHEN stock_quantity <= min_stock_level THEN 1 ELSE 0 END) as low_count
    FROM products");
$stats = mysqli_fetch_assoc($stats_res);

$total_unique_items = $stats['total_unique'] ?? 0;
$total_stock_count = $stats['total_units'] ?? 0;
$total_inventory_value = $stats['total_val'] ?? 0;
$low_count = $stats['low_count'] ?? 0;

// 4. Cart Count for the button
$cart_items_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inventory Dashboard - <?php echo SHOP_NAME; ?></title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 20px; background-color: #f9f9f9; margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        th, td { border: 1px solid #eee; padding: 12px; text-align: left; }
        th { background-color: #5d4037; color: white; font-size: 14px; text-transform: uppercase; }
        tr:hover { background-color: #fcf9f5; }
        .low-stock-row { background-color: #fff5f5; }
        
        .badge-admin { background: #fee2e2 !important; color: #991b1b !important; }
        .badge-staff { background: #e0f2fe !important; color: #075985 !important; }
        
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-top: 20px; }
        .notif-bell { position: relative; display: inline-block; cursor: pointer; font-size: 24px; text-decoration: none; }
        .notif-badge { position: absolute; top: -5px; right: -5px; background: #dc3545; color: white; border-radius: 50%; padding: 2px 6px; font-size: 12px; font-weight: bold; }
        
        .stat-container { display: flex; gap: 20px; margin: 20px 0; }
        .stat-card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); flex: 1; border-top: 4px solid #d2a679; }
        .stat-label { color: #666; font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .stat-val { font-size: 24px; font-weight: bold; display: block; margin-top: 10px; color: #333; }
        
        .action-btn { padding: 10px 18px; border-radius: 5px; text-decoration: none; font-weight: bold; font-size: 14px; transition: 0.3s; display: inline-block; }
        .btn-add { background: #d2a679; color: white; }
        .btn-report { background: #5d4037; color: white; margin-left: 10px; }
        .btn-cart { background: #2563eb; color: white; margin-left: 10px; }
        
        .stock-badge { padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; display: inline-block; margin-top: 5px; }
    </style>
</head>
<body>

    <?php include 'navigation.php'; ?>

    <div style="margin-bottom: 20px; padding: 20px; background: white; border-radius: 8px; border-left: 6px solid #d2a679; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
        <div>
            <h2 style="margin: 0; color: #5d4037;">Welcome back, <?php echo htmlspecialchars($_SESSION['admin']); ?>!</h2>
            <p style="margin: 5px 0 0 0; color: #666; font-size: 14px;">
                Logged in as: <span class="badge <?php echo ($_SESSION['role'] == 'admin') ? 'badge-admin' : 'badge-staff'; ?>" style="padding: 3px 10px; border-radius: 4px; font-weight: bold;">
                    <?php echo ucfirst($_SESSION['role']); ?>
                </span>
            </p>
        </div>
        <div style="text-align: right; color: #888;">
            <div style="font-weight: bold; color: #555;"><?php echo date('l, d M Y'); ?></div>
            <div style="font-size: 12px;">System Status: Online</div>
        </div>
    </div>

    <div class="header-flex">
        <h1 style="color: #5d4037; margin: 0;">📦 Inventory Overview</h1>
        <div>
            <a href="index.php?filter=low_stock" class="notif-bell" title="Low Stock Alerts">
                🔔 <?php if($low_count > 0): ?><span class="notif-badge"><?php echo $low_count; ?></span><?php endif; ?>
            </a>
        </div>
    </div>

    <div class="stat-container">
        <div class="stat-card" style="border-top-color: #8b4513;">
            <span class="stat-label">Total Inventory Value</span>
            <span class="stat-val" style="color: #8b4513;"><?php echo number_format($total_inventory_value, 2) . " " . CURRENCY; ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Unique Products</span>
            <span class="stat-val"><?php echo $total_unique_items; ?></span>
        </div>
        <div class="stat-card" style="border-top-color: #28a745;">
            <span class="stat-label">Units in Stock</span>
            <span class="stat-val"><?php echo (int)$total_stock_count; ?></span>
        </div>
    </div>

    <div style="margin: 25px 0; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <a href="add_product.php" class="action-btn btn-add">+ New Item</a>
            <a href="reports.php" class="action-btn btn-report">📊 Sales Reports</a>
            <a href="cart.php" class="action-btn btn-cart">🛒 Cart (<?php echo $cart_items_count; ?>)</a>
        </div>
        
        <form method="GET" style="display: flex; gap: 5px;">
            <input type="text" name="search" placeholder="Search by name or SKU..." value="<?php echo htmlspecialchars($search); ?>" style="padding: 10px; width: 250px; border: 1px solid #ddd; border-radius: 5px;">
            <button type="submit" style="padding: 10px 15px; background: #5d4037; color: white; border: none; border-radius: 5px; cursor: pointer;">🔍</button>
            <?php if($filter || $search): ?> 
                <a href="index.php" style="padding: 10px; color: #666; text-decoration: none; font-size: 13px;">Clear</a> 
            <?php endif; ?>
        </form>
    </div>

    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
        <div style="background: #fee2e2; color: #b91c1c; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #fecaca; font-weight: bold;">
            🗑️ Item successfully removed from inventory.
        </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Preview</th>
                <th>SKU</th>
                <th>Item Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock Level</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            while($row = mysqli_fetch_assoc($result)) {
                $img = !empty($row['image_path']) ? $row['image_path'] : 'default.png';
                $fragile = ($row['is_fragile']) ? " <span title='Fragile Item' style='cursor:help;'>⚠️</span>" : "";
                
                $is_low = ($row['stock_quantity'] <= $row['min_stock_level']);
                $row_class = $is_low ? 'low-stock-row' : '';
                
                // Badge Logic
                $stock_badge = "";
                if ($row['stock_quantity'] <= 0) {
                    $stock_badge = "<span class='stock-badge' style='background: #000; color: #fff;'>OUT OF STOCK</span>";
                } elseif ($is_low) {
                    $stock_badge = "<span class='stock-badge' style='background: #dc3545; color: #fff;'>LOW STOCK</span>";
                }
            ?>
            <tr class="<?php echo $row_class; ?>">
                <td><img src="uploads/<?php echo $img; ?>" width="50" height="50" style="object-fit:cover; border-radius:5px; border: 1px solid #eee;"></td>
                <td style="font-family: monospace; font-weight: bold; color: #666;"><?php echo $row['sku']; ?></td>
                <td><strong><?php echo $row['item_name']; ?></strong><?php echo $fragile; ?></td>
                <td><span style="font-size: 13px; color: #777;"><?php echo $row['category']; ?></span></td>
                <td><?php echo number_format($row['price'], 2) . " " . CURRENCY; ?></td>
                <td>
                    <span style="font-size: 16px; font-weight: bold;"><?php echo $row['stock_quantity']; ?></span>
                    <br><?php echo $stock_badge; ?>
                </td>
                <td>
                    <a href="cart.php?action=add&id=<?php echo $row['id']; ?>" style="color: #2563eb; text-decoration: none; font-weight: bold;">Sell</a> | 
                    <a href="restock.php?id=<?php echo $row['id']; ?>" style="color: #059669; text-decoration: none; font-weight: bold;">Restock</a>
                    
                    <?php if($_SESSION['role'] === 'admin'): ?>
                        | <a href="edit_product.php?id=<?php echo $row['id']; ?>" style="color: #4b5563; text-decoration: none;">Edit</a>
                        | <a href="delete_product.php?id=<?php echo $row['id']; ?>" style="color: #dc3545; text-decoration: none;" onclick="return confirm('Permanently delete this item?');">Delete</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>

    <?php
    // Low Stock Alert Script
    if ($low_count > 0 && $filter != 'low_stock'): 
    ?>
    <script>
        window.onload = function() {
            if (confirm("⚠️ You have <?php echo $low_count; ?> items with low stock. Would you like to view them now?")) {
                window.location.href = "index.php?filter=low_stock";
            }
        };
    </script>
    <?php endif; ?>

</body>
</html>