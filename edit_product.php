<?php
session_start();
if(!isset($_SESSION['admin'])){ header("Location: login.php"); exit(); }
include 'db_connect.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 1. Fetch current product details
$res = mysqli_query($conn, "SELECT * FROM products WHERE id = $id");
$product = mysqli_fetch_assoc($res);

if(!$product) { die("<h2 style='text-align:center; margin-top:50px;'>Product not found.</h2>"); }

// 2. Handle Update Logic
if(isset($_POST['update'])){
    $name = mysqli_real_escape_string($conn, $_POST['item_name']);
    $cat  = mysqli_real_escape_string($conn, $_POST['category']);
    $sup_id = (int)$_POST['supplier_id']; // Added to link with your suppliers table
    $buy_price = (float)$_POST['buy_price'];
    $price = (float)$_POST['price'];
    $min_stock = (int)$_POST['min_stock'];
    $is_fragile = isset($_POST['is_fragile']) ? 1 : 0;

    $image_query = "";
    if(!empty($_FILES['product_image']['name'])){
        $ext = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
        $img_name = time() . "_" . $product['sku'] . "." . $ext;
        if(move_uploaded_file($_FILES['product_image']['tmp_name'], "uploads/" . $img_name)){
             $image_query = ", image_path = '$img_name'";
        }
    }

    $sql = "UPDATE products SET 
            item_name = '$name', 
            category = '$cat', 
            supplier_id = '$sup_id',
            buy_price = '$buy_price', 
            price = '$price', 
            min_stock_level = '$min_stock', 
            is_fragile = '$is_fragile' 
            $image_query 
            WHERE id = $id";

    if(mysqli_query($conn, $sql)){
        header("Location: index.php?msg=updated");
        exit();
    }
}

// 3. Fetch Suppliers for the dropdown
$suppliers_res = mysqli_query($conn, "SELECT id, supplier_name FROM suppliers ORDER BY supplier_name ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Item - <?php echo SHOP_NAME; ?></title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f7f6; padding: 20px; }
        .edit-card { background: white; padding: 30px; border-radius: 10px; max-width: 550px; margin: auto; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-top: 5px solid #8b4513; }
        label { font-weight: bold; color: #5d4037; display: block; margin-top: 15px; font-size: 14px; }
        input, select { width: 100%; padding: 12px; margin-top: 5px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .btn-update { background: #8b4513; color: white; border: none; padding: 15px; width: 100%; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 16px; margin-top: 25px; transition: 0.3s; }
        .btn-update:hover { background: #5d4037; }
        .current-img { margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; padding: 5px; }
    </style>
</head>
<body>

<?php include 'navigation.php'; ?>

<div class="edit-card">
    <a href="index.php" style="text-decoration:none; color:#8b4513; font-weight:bold;">← Back to Dashboard</a>
    <h2 style="color: #8b4513; margin-top: 15px;">Edit Product: <?php echo htmlspecialchars($product['sku']); ?></h2>
    
    <form method="POST" enctype="multipart/form-data">
        <label>Item Name:</label>
        <input type="text" name="item_name" value="<?php echo htmlspecialchars($product['item_name']); ?>" required>

        <div style="display: flex; gap: 15px;">
            <div style="flex: 1;">
                <label>Category:</label>
                <select name="category">
                    <option value="Home Decor" <?php if($product['category'] == 'Home Decor') echo 'selected'; ?>>Home Decor</option>
                    <option value="Gift Items" <?php if($product['category'] == 'Gift Items') echo 'selected'; ?>>Gift Items</option>
                    <option value="Vases" <?php if($product['category'] == 'Vases') echo 'selected'; ?>>Vases</option>
                </select>
            </div>
            <div style="flex: 1;">
                <label>Supplier:</label>
                <select name="supplier_id" required>
                    <?php while($s = mysqli_fetch_assoc($suppliers_res)): ?>
                        <option value="<?php echo $s['id']; ?>" <?php echo ($product['supplier_id'] == $s['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($s['supplier_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>

        <div style="display: flex; gap: 15px;">
            <div style="flex: 1;">
                <label>Buy Price (Cost):</label>
                <input type="number" name="buy_price" step="0.01" value="<?php echo $product['buy_price']; ?>" required>
            </div>
            <div style="flex: 1;">
                <label>Sell Price (<?php echo CURRENCY; ?>):</label>
                <input type="number" name="price" step="0.01" value="<?php echo $product['price']; ?>" required>
            </div>
        </div>

        <label>Min Stock Alert Level:</label>
        <input type="number" name="min_stock" value="<?php echo $product['min_stock_level']; ?>" required>

        <label style="display: flex; align-items: center; font-weight: normal; cursor: pointer; margin: 15px 0;">
            <input type="checkbox" name="is_fragile" value="1" <?php echo ($product['is_fragile']) ? 'checked' : ''; ?> style="width: auto; margin-right: 10px;"> 
            This is a Fragile Item (Mark with Icon)
        </label>

        <label>Product Image:</label>
        <?php if(!empty($product['image_path'])): ?>
            <img src="uploads/<?php echo $product['image_path']; ?>" class="current-img" width="80" alt="current">
            <span style="font-size: 11px; color: #888;">Current Image</span>
        <?php endif; ?>
        <input type="file" name="product_image" accept="image/*">

        <button type="submit" name="update" class="btn-update">Update Product Information</button>
    </form>
</div>

</body>
</html>