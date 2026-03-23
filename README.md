📦 Shokher Ghor - Inventory & POS System
A professional Inventory Management and Point of Sale (POS) system built for handicraft and retail shops. This system tracks stock, manages suppliers, records sales with customer details, and generates printable receipts and audit trails.

🚀 Features
Secure Authentication: Role-based access (Admin/Staff) with hashed passwords.

Inventory Management: Full CRUD operations with image uploads and SKU tracking.

POS System: Session-based cart with real-time stock validation and VAT calculation.

Audit Trail: Permanent logs of all stock movements (In/Out) for accountability.

CRM Lite: Search purchase history by customer phone number.

Supplier Management: Link products to specific vendors.

Reports: Statistical dashboard and printable receipts.

🛠️ Installation Guide (Hassle-Free)
Follow these steps to install the project on a new machine using XAMPP.

1. Prerequisites
Install XAMPP (Version with PHP 8.1 or higher recommended).

A web browser (Chrome, Edge, etc.).

2. Project Setup
Download or Clone this repository.

Copy the entire shokher_ghor folder.

Paste it into your XAMPP's web directory:

C:\xampp\htdocs\shokher_ghor

3. Database Configuration
Open XAMPP Control Panel and start Apache and MySQL.

Go to your browser and open http://localhost/phpmyadmin/.

Create a new database named shokher_ghor_db.

Click on the newly created database and go to the Import tab.

Choose the shokher_ghor_db.sql file located in your project folder and click Import/Go.

4. File Permissions
Navigate to C:\xampp\htdocs\shokher_ghor\.

Create a folder named uploads if it does not exist.

Ensure this folder has write permissions (In Windows, XAMPP usually handles this automatically).

5. Launch the Application
Open your browser and type:

http://localhost/shokher_ghor/

🔑 Default Credentials
Use these to log in for the first time:

Username: admin

Password: password123

Reset Password: http://localhost/shokher_ghor/reset_admin.php

📁 Project Structure
db_connect.php - Centralized database connection and shop settings.

index.php - Dashboard with inventory stats and product list.

cart.php - Point of Sale interface.

process_checkout.php - Transaction-safe logic for handling sales.

audit_logs.php - Immutable record of all system activity.

receipt.php - Printable invoice generator.

🛡️ Security Features
SQL Injection Protection: All inputs are sanitized using mysqli_real_escape_string.

Password Security: Passwords are never stored in plain text; password_hash and password_verify are used.

Race Condition Prevention: Uses FOR UPDATE and START TRANSACTION during checkout to prevent overselling items.
