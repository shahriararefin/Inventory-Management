<?php
session_start();
// 1. Protection: Only admins should have the power to delete
if(!isset($_SESSION['admin']) || $_SESSION['role'] !== 'admin'){ 
    header("Location: login.php"); 
    exit(); 
}
include 'db_connect.php';

if(isset($_GET['id'])){
    $id = (int)$_GET['id'];
    
    // 2. Fetch the image filename before deleting the product record
    $res = mysqli_query($conn, "SELECT image_path FROM products WHERE id = $id");
    $product = mysqli_fetch_assoc($res);

    if($product) {
        // 3. Delete the physical image file from the server
        $image_file = "uploads/" . $product['image_path'];
        if($product['image_path'] != 'default.png' && file_exists($image_file)){
            unlink($image_file); // This physically removes the file
        }

        // 4. Delete the product
        // Note: If your inventory_logs table has a FOREIGN KEY with ON DELETE CASCADE,
        // you don't need to manually delete logs. If not, keep your log deletion line.
        mysqli_query($conn, "DELETE FROM inventory_logs WHERE product_id = $id");
        
        $sql = "DELETE FROM products WHERE id = $id";
        
        if(mysqli_query($conn, $sql)){
            header("Location: index.php?msg=deleted");
            exit();
        } else {
            echo "Error deleting record: " . mysqli_error($conn);
        }
    }
} else {
    header("Location: index.php");
}
?>