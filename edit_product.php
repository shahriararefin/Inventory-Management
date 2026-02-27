<?php
session_start();
if(!isset($_SESSION['admin'])){ header("Location: login.php"); exit(); }
include 'db_connect.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$res = mysqli_query($conn, "SELECT * FROM products WHERE id = $id");
$product = mysqli_fetch_assoc($res);

if(!$product) { die("Product not found."); }

if(isset($_POST['update'])){
    $name = mysqli_real_escape_string($conn, $_POST['item_name']);
    $cat  = mysqli_real_escape_string($conn, $_POST['category']);
    $buy_price = (float)$_POST['buy_price']; // Added
    $price = (float)$_POST['price'];
    $min_stock = (int)$_POST['min_stock'];
    $is_fragile = isset($_POST['is_fragile']) ? 1 : 0;

    // Optional: Image update logic
    $image_query = "";
    if(!empty($_FILES['product_image']['name'])){
        $img_name = time() . "_" . $_FILES['product_image']['name']; // Added timestamp for safety
        move_uploaded_file($_FILES['product_image']['tmp_name'], "uploads/" . $img_name);
        $image_query = ", image_path = '$img_name'";
    }

    $sql = "UPDATE products SET 
            item_name = '$name', 
            category = '$cat', 
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
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Item - Shokher Ghor</title>
    <style>
        body { font-family: Arial; background: #fdf5e6; padding: 40px; }
        .edit-card { background: white; padding: 30px; border-radius: 10px; max-width: 500px; margin: auto; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        label { font-weight: bold; color: #555; display: block; margin-top: 10px; }
        input, select { width: 100%; padding: 12px; margin: 8px 0 15px 0; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        button { background: #d2a679; color: white; border: none; padding: 15px; width: 100%; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 16px; }
        button:hover { background: #bc936a; }
    </style>
</head>
<body>

<div class="edit-card">
    <a href="index.php" style="text-decoration:none; color:#8b4513;">← Back to Dashboard</a>
    <h2 style="color: #8b4513;">Edit Product Details</h2>
    
    <form method="POST" enctype="multipart/form-data">
        <label>Item Name:</label>
        <input type="text" name="item_name" value="<?php echo htmlspecialchars($product['item_name']); ?>" required>

        <label>Category:</label>
        <select name="category">
            <option value="Home Decor" <?php if($product['category'] == 'Home Decor') echo 'selected'; ?>>Home Decor</option>
            <option value="Gift Items" <?php if($product['category'] == 'Gift Items') echo 'selected'; ?>>Gift Items</option>
            <option value="Vases" <?php if($product['category'] == 'Vases') echo 'selected'; ?>>Vases</option>
        </select>

        <div style="display: flex; gap: 15px;">
            <div style="flex: 1;">
                <label>Buy Price (Cost):</label>
                <input type="number" name="buy_price" step="0.01" value="<?php echo $product['buy_price']; ?>" required>
            </div>
            <div style="flex: 1;">
                <label>Sell Price (BDT):</label>
                <input type="number" name="price" step="0.01" value="<?php echo $product['price']; ?>" required>
            </div>
        </div>

        <label>Min Stock Alert Level:</label>
        <input type="number" name="min_stock" value="<?php echo $product['min_stock_level']; ?>" required>

        <label style="display: inline-block; margin-bottom: 20px; font-weight: normal; cursor: pointer;">
            <input type="checkbox" name="is_fragile" value="1" <?php echo ($product['is_fragile']) ? 'checked' : ''; ?> style="width: auto; margin-right: 10px;"> 
            This is a Fragile Item
        </label>

        <label>Change Image (Leave blank to keep current):</label>
        <input type="file" name="product_image" accept="image/*">

        <button type="submit" name="update">Update Product Info</button>
    </form>
</div>

</body>
</html>