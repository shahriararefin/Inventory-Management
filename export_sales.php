<?php
session_start();
if(!isset($_SESSION['admin'])){ exit("Access Denied"); }
include 'db_connect.php';

// 1. Get the same filters used in your reports.php
$start = $_GET['start_date'] ?? '';
$end = $_GET['end_date'] ?? '';
$date_condition = " l.type = 'OUT' ";

if(!empty($start) && !empty($end)) {
    $date_condition .= " AND l.date_added BETWEEN '$start 00:00:00' AND '$end 23:59:59' ";
}

// 2. Fetch Data
$query = "SELECT l.id, p.item_name, l.quantity, p.price, (p.price * l.quantity) as total, l.date_added 
          FROM inventory_logs l 
          JOIN products p ON l.product_id = p.id 
          WHERE $date_condition ORDER BY l.date_added DESC";
$result = mysqli_query($conn, $query);

// 3. Set headers to force download
$filename = "Sales_Report_" . date('Y-m-d') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

// 4. Open "output" stream
$output = fopen('php://output', 'w');

// 5. Set Column Headers in the CSV
fputcsv($output, array('Invoice ID', 'Product Name', 'Quantity', 'Unit Price', 'Total Amount', 'Date'));

// 6. Loop through database and add to CSV
while($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, $row);
}

fclose($output);
exit();
?>