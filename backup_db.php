<?php
session_start();
if(!isset($_SESSION['admin'])){ exit("Access Denied"); }
include 'db_connect.php';

// 1. Get all table names
$tables = array();
$result = mysqli_query($conn, "SHOW TABLES");
while($row = mysqli_fetch_row($result)){
    $tables[] = $row[0];
}

$return = "-- Shokher Ghor Database Backup\n";
$return .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";

// 2. Loop through tables to generate SQL
foreach($tables as $table){
    // Structure
    $result = mysqli_query($conn, "SHOW CREATE TABLE $table");
    $row = mysqli_fetch_row($result);
    $return .= "\n\n" . $row[1] . ";\n\n";
    
    // Data
    $result = mysqli_query($conn, "SELECT * FROM $table");
    $num_fields = mysqli_num_fields($result);
    
    for($i = 0; $i < $num_fields; $i++){
        while($row = mysqli_fetch_row($result)){
            $return .= "INSERT INTO $table VALUES(";
            for($j=0; $j<$num_fields; $j++){
                $row[$j] = addslashes($row[$j]);
                $row[$j] = str_replace("\n","\\n",$row[$j]);
                if (isset($row[$j])) { $return .= '"'.$row[$j].'"' ; } else { $return .= '""'; }
                if ($j<($num_fields-1)) { $return.= ','; }
            }
            $return .= ");\n";
        }
    }
}

// 3. Force Download
$filename = "backup_" . SHOP_NAME . "_" . date('Y-m-d') . ".sql";
header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary");
header("Content-disposition: attachment; filename=\"" . $filename . "\"");
echo $return;
exit;
?>