<?php 
// 1. Get the current filename (e.g., 'index.php')
$current_page = basename($_SERVER['PHP_SELF']); 

// 2. Count cart items for the badge
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;

// 3. Get the user role for easy access
$role = $_SESSION['role'] ?? 'staff';
?>

<style>
    .top-nav {
        background-color: #5d4037;
        padding: 0;
        text-align: center;
        margin-bottom: 30px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
    
    .nav-list {
        list-style: none;
        margin: 0;
        padding: 0;
        display: inline-flex;
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .nav-list a {
        color: #fdf5e6;
        padding: 18px 25px;
        text-decoration: none;
        font-weight: bold;
        display: block;
        transition: 0.3s;
        font-size: 14px;
    }

    .nav-list a.active {
        background-color: #d2a679;
        color: #5d4037;
        border-bottom: 4px solid #8b4513;
    }

    .nav-list a:hover:not(.active) {
        background-color: #8b4513;
    }

    .cart-badge {
        background: #28a745;
        color: white;
        padding: 2px 6px;
        border-radius: 10px;
        font-size: 11px;
        margin-left: 5px;
    }
</style>

<div class="top-nav">
    <div class="nav-list">
        <a href="index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">🏠 Dashboard</a>
        
        <a href="cart.php" class="<?php echo ($current_page == 'cart.php') ? 'active' : ''; ?>">
            🛒 Cart <?php if($cart_count > 0): ?><span class="cart-badge"><?php echo $cart_count; ?></span><?php endif; ?>
        </a>
        <a href="customer_history.php">🔍 History</a>
        <a href="reports.php" class="<?php echo ($current_page == 'reports.php') ? 'active' : ''; ?>">📊 Sales Reports</a>
        
        <a href="margins.php" class="<?php echo ($current_page == 'margins.php') ? 'active' : ''; ?>">💰 Profit Margins</a>
        
        <?php if($role === 'admin'): ?>
            <a href="audit_logs.php" class="<?php echo ($current_page == 'audit_logs.php') ? 'active' : ''; ?>">📜 Audit Logs</a>
            <a href="manage_users.php" class="<?php echo ($current_page == 'manage_users.php') ? 'active' : ''; ?>">👥 Manage Users</a>
            
            <a href="settings.php" class="<?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>">⚙️ Settings</a>
            <?php if($role === 'admin'): ?>
    <a href="manage_suppliers.php">Suppliers</a>
    <?php endif; ?>
            <?php endif; ?>
        <a href="change_password.php">🔐 Change Password</a>        
        <a href="logout.php" style="color: #ff9999;">🚪 Logout</a>
    </div>
</div>