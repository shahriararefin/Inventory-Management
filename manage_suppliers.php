<?php
session_start();
include 'db_connect.php';

// Protection: Only Admins manage suppliers
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("<h1 style='color:red; text-align:center; padding:50px;'>Access Denied!</h1>");
}

// Handle Adding Supplier
if(isset($_POST['add_supplier'])){
    $name = mysqli_real_escape_string($conn, $_POST['s_name']);
    $contact = mysqli_real_escape_string($conn, $_POST['s_contact']);
    $phone = mysqli_real_escape_string($conn, $_POST['s_phone']);
    $email = mysqli_real_escape_string($conn, $_POST['s_email']);

    $sql = "INSERT INTO suppliers (supplier_name, contact_person, phone, email) 
            VALUES ('$name', '$contact', '$phone', '$email')";
    
    if(mysqli_query($conn, $sql)){
        $msg = "✅ Supplier added successfully!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Suppliers - <?php echo SHOP_NAME; ?></title>
    <style>
        body { font-family: 'Segoe UI', Arial; background: #f4f7f6; padding: 20px; }
        .container { background: white; padding: 25px; border-radius: 8px; max-width: 900px; margin: auto; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { background: #5d4037; color: white; border: none; padding: 12px 20px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 30px; }
        th, td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
        th { background: #d2a679; color: white; font-size: 13px; }
        .contact-info { font-size: 13px; color: #666; }
    </style>
</head>
<body>

<?php include 'navigation.php'; ?>

<div class="container">
    <h2>🚚 Supplier Directory</h2>
    <?php if(isset($msg)) echo "<p style='color:green;'>$msg</p>"; ?>

    <form method="POST">
        <div class="form-grid">
            <input type="text" name="s_name" placeholder="Supplier Company Name" required>
            <input type="text" name="s_contact" placeholder="Contact Person Name">
            <input type="text" name="s_phone" placeholder="Phone Number" required>
            <input type="email" name="s_email" placeholder="Email Address">
        </div>
        <button type="submit" name="add_supplier">+ Add Supplier</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Supplier Name</th>
                <th>Contact</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Products Linked</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Join to count how many products belong to each supplier
            $res = mysqli_query($conn, "SELECT s.*, COUNT(p.id) as p_count 
                                      FROM suppliers s 
                                      LEFT JOIN products p ON s.id = p.supplier_id 
                                      GROUP BY s.id");
            while($row = mysqli_fetch_assoc($res)){
                echo "<tr>
                        <td>#{$row['id']}</td>
                        <td><strong>" . htmlspecialchars($row['supplier_name']) . "</strong></td>
                        <td>" . htmlspecialchars($row['contact_person']) . "</td>
                        <td>" . htmlspecialchars($row['phone']) . "</td>
                        <td>" . htmlspecialchars($row['email']) . "</td>
                        <td><span style='background:#eee; padding:2px 8px; border-radius:10px; font-size:12px;'>{$row['p_count']} items</span></td>
                      </tr>";
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>