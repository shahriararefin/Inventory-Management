<?php
session_start();
// Security check
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}
include 'db_connect.php';

if(isset($_GET['id'])){
    $id = (int)$_GET['id'];
    
    // First, we should delete any logs related to this product to maintain referential integrity
    mysqli_query($conn, "DELETE FROM inventory_logs WHERE product_id = $id");
    
    // Now delete the product
    $sql = "DELETE FROM products WHERE id = $id";
    
    if(mysqli_query($conn, $sql)){
        header("Location: index.php?msg=deleted");
    } else {
        echo "Error deleting record: " . mysqli_error($conn);
    }
} else {
    header("Location: index.php");
}
exit();
?>