<?php
session_start();
if(!isset($_SESSION['admin'])){ header("Location: login.php"); exit(); }
include 'db_connect.php';

if(isset($_POST['bulk_delete'])){
    $type = $_POST['delete_type'];

    if($type == 'all'){
        $sql = "TRUNCATE TABLE inventory_logs";
    } elseif($type == 'old'){
        $sql = "DELETE FROM inventory_logs WHERE date_added < DATE_SUB(NOW(), INTERVAL 30 DAY)";
    }

    if(mysqli_query($conn, $sql)){
        header("Location: reports.php?msg=bulk_deleted");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
} else {
    header("Location: reports.php");
}
?>