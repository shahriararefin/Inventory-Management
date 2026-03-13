<?php
session_start();
if(!isset($_SESSION['admin'])){ header("Location: login.php"); exit(); }
include 'db_connect.php';

// --- CART LOGIC ---

// 1. Add/Update Item
if(isset($_GET['action']) && $_GET['action'] == "add") {
    $id = (int)$_GET['id'];
    $res = mysqli_query($conn, "SELECT item_name, price, stock_quantity FROM products WHERE id = $id");
    $product = mysqli_fetch_assoc($res);
    
    if($product) {
        if(isset($_SESSION['cart'][$id])) {
            // Check if adding one more exceeds stock
            if($_SESSION['cart'][$id]['qty'] < $product['stock_quantity']) {
                $_SESSION['cart'][$id]['qty']++;
            }
        } else {
            $_SESSION['cart'][$id] = [
                'name' => $product['item_name'],
                'price' => $product['price'],
                'qty' => 1,
                'max' => $product['stock_quantity']
            ];
        }
    }
    header("Location: cart.php");
    exit();
}

// 2. Update Quantity Manually
if(isset($_POST['update_qty'])) {
    foreach($_POST['qty'] as $id => $new_qty) {
        $id = (int)$id;
        $new_qty = (int)$new_qty;
        
        if($new_qty <= 0) {
            unset($_SESSION['cart'][$id]);
        } else {
            // Re-verify stock limit to prevent overselling
            $res = mysqli_query($conn, "SELECT stock_quantity FROM products WHERE id = $id");
            $row = mysqli_fetch_assoc($res);
            if($new_qty <= $row['stock_quantity']) {
                $_SESSION['cart'][$id]['qty'] = $new_qty;
            }
        }
    }
    header("Location: cart.php?msg=updated");
    exit();
}

// 3. Remove Item
if(isset($_GET['action']) && $_GET['action'] == "remove") {
    $id = (int)$_GET['id'];
    unset($_SESSION['cart'][$id]);
    header("Location: cart.php");
    exit();
}

// 4. Clear Cart
if(isset($_GET['action']) && $_GET['action'] == "clear") {
    unset($_SESSION['cart']);
    header("Location: cart.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cart - <?php echo SHOP_NAME; ?></title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; padding: 20px; background: #f4f7f6; }
        .cart-card { background: white; padding: 30px; border-radius: 10px; max-width: 900px; margin: auto; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 15px; border-bottom: 1px solid #eee; text-align: left; }
        th { background: #5d4037; color: white; text-transform: uppercase; font-size: 13px; }
        .qty-input { width: 65px; padding: 8px; text-align: center; border: 1px solid #ddd; border-radius: 4px; }
        .btn-update { background: #d2a679; color: #5d4037; border: none; padding: 10px 20px; cursor: pointer; border-radius: 4px; font-weight: bold; transition: 0.3s; }
        .btn-update:hover { background: #bc936a; }
        .btn-checkout { background: #28a745; color: white; padding: 18px; width: 100%; border: none; border-radius: 6px; cursor: pointer; font-size: 20px; font-weight: bold; margin-top: 20px; transition: 0.3s; }
        .btn-checkout:hover { background: #218838; }
        .btn-clear { color: #dc3545; text-decoration: none; font-size: 13px; font-weight: bold; }
        .total-row td { border-bottom: none; padding: 5px 15px; }
    </style>
</head>
<body>

<?php include 'navigation.php'; ?>

<div class="cart-card">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #eee; padding-bottom: 15px;">
        <h2 style="color: #5d4037; margin: 0;">🛒 Point of Sale (POS)</h2>
        <a href="cart.php?action=clear" class="btn-clear" onclick="return confirm('Empty cart?')">Clear Sale</a>
    </div>

    <?php if(!empty($_SESSION['cart'])): ?>
        <form method="POST">
            <table>
                <thead>
                    <tr>
                        <th>Item Description</th>
                        <th>Unit Price</th>
                        <th>Qty</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $subtotal_sum = 0;
                    foreach($_SESSION['cart'] as $id => $item): 
                        $current_subtotal = $item['price'] * $item['qty'];
                        $subtotal_sum += $current_subtotal;
                    ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($item['name']); ?></strong></td>
                        <td><?php echo number_format($item['price'], 2); ?></td>
                        <td>
                            <input type="number" name="qty[<?php echo $id; ?>]" value="<?php echo $item['qty']; ?>" min="1" class="qty-input">
                        </td>
                        <td><?php echo number_format($current_subtotal, 2); ?></td>
                        <td><a href="cart.php?action=remove&id=<?php echo $id; ?>" style="color:#dc3545; text-decoration:none; font-size: 20px;">&times;</a></td>
                    </tr>
                    <?php endforeach; ?>

                    <?php 
                        $vat_amount = ($subtotal_sum * TAX_RATE) / 100;
                        $final_total = $subtotal_sum + $vat_amount;
                    ?>

                    <tr class="total-row"><td colspan="5" style="padding-top: 20px;"></td></tr>
                    <tr class="total-row">
                        <td colspan="3" style="text-align: right; color: #666;">Subtotal:</td>
                        <td colspan="2"><?php echo number_format($subtotal_sum, 2); ?></td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="3" style="text-align: right; color: #666;">VAT (<?php echo TAX_RATE; ?>%):</td>
                        <td colspan="2"><?php echo number_format($vat_amount, 2); ?></td>
                    </tr>
                    <tr class="total-row" style="background: #fdf5e6; font-size: 22px; font-weight: bold; color: #8b4513;">
                        <td colspan="3" style="text-align: right;">GRAND TOTAL:</td>
                        <td colspan="2"><?php echo number_format($final_total, 2); ?> <?php echo CURRENCY; ?></td>
                    </tr>
                </tbody>
            </table>
            
            <div style="text-align: right;">
                <button type="submit" name="update_qty" class="btn-update">🔄 Update Prices</button>
            </div>
        </form>

        <form action="process_checkout.php" method="POST" style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
    <h4 style="color: #5d4037; margin-bottom: 10px;">👤 Customer Information (Optional)</h4>
    <div style="display: flex; gap: 15px; margin-bottom: 20px;">
        <input type="text" name="cust_name" placeholder="Customer Name" style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
        <input type="text" name="cust_phone" placeholder="Phone Number" style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
    </div>
    <button type="submit" class="btn-checkout">Complete Sale & Print Receipt</button>
</form>

    <?php else: ?>
        <div style="text-align: center; padding: 60px;">
            <p style="color: #999; font-size: 18px;">The cart is empty.</p>
            <a href="index.php" style="color: #d2a679; font-weight: bold; text-decoration: none; border: 2px solid #d2a679; padding: 10px 20px; border-radius: 5px;">← Browse Inventory</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>