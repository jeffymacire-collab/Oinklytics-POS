<?php
$host = '127.0.0.1';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("CREATE DATABASE IF NOT EXISTS oinklytics_pos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE oinklytics_pos");

    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(100) NOT NULL,
        role ENUM('admin','supervisor','cashier') NOT NULL DEFAULT 'cashier',
        status VARCHAR(20) NOT NULL DEFAULT 'Active',
        last_login VARCHAR(50) DEFAULT 'Never',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        code VARCHAR(20) PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        category VARCHAR(50) NOT NULL,
        unit VARCHAR(20) NOT NULL DEFAULT 'kg',
        price DECIMAL(10,2) NOT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'Active',
        stock DECIMAL(10,2) NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    $pdo->exec("CREATE TABLE IF NOT EXISTS customers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        type VARCHAR(50) DEFAULT 'Retail',
        area VARCHAR(100) DEFAULT '',
        birthday VARCHAR(20) DEFAULT '',
        preferred TEXT DEFAULT '',
        points INT DEFAULT 0,
        spent DECIMAL(12,2) DEFAULT 0,
        consent TINYINT(1) DEFAULT 1,
        registered VARCHAR(20) DEFAULT '',
        last_visit VARCHAR(20) DEFAULT 'Today',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    $pdo->exec("CREATE TABLE IF NOT EXISTS batches (
        id VARCHAR(50) PRIMARY KEY,
        source VARCHAR(100) NOT NULL,
        weight DECIMAL(10,2) NOT NULL,
        current_weight DECIMAL(10,2) NOT NULL,
        use_by VARCHAR(20) NOT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'Active',
        cost_per_kg DECIMAL(10,2) NOT NULL,
        inspection VARCHAR(50) DEFAULT '',
        received_by VARCHAR(100) DEFAULT '',
        doc_ref VARCHAR(50) DEFAULT '',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    $pdo->exec("CREATE TABLE IF NOT EXISTS sales (
        id INT AUTO_INCREMENT PRIMARY KEY,
        receipt_no VARCHAR(50) NOT NULL,
        total DECIMAL(10,2) NOT NULL,
        payment_method VARCHAR(20) NOT NULL,
        mpesa_ref VARCHAR(50) DEFAULT '',
        cash_amount DECIMAL(10,2) DEFAULT 0,
        mpesa_amount DECIMAL(10,2) DEFAULT 0,
        customer_id INT DEFAULT NULL,
        batch_id VARCHAR(50) DEFAULT NULL,
        items JSON DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    $pdo->exec("CREATE TABLE IF NOT EXISTS audit_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        time VARCHAR(30) NOT NULL,
        user VARCHAR(50) NOT NULL,
        role VARCHAR(20) NOT NULL,
        action VARCHAR(50) NOT NULL,
        description TEXT DEFAULT '',
        ip VARCHAR(50) DEFAULT '',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    $pdo->exec("CREATE TABLE IF NOT EXISTS wastage (
        id INT AUTO_INCREMENT PRIMARY KEY,
        date VARCHAR(20) NOT NULL,
        product VARCHAR(100) NOT NULL,
        batch_id VARCHAR(50) DEFAULT NULL,
        qty DECIMAL(10,2) NOT NULL,
        reason VARCHAR(100) DEFAULT '',
        supervisor VARCHAR(100) DEFAULT '',
        status VARCHAR(20) DEFAULT 'Approved',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        type VARCHAR(20) NOT NULL,
        icon VARCHAR(50) DEFAULT '',
        color VARCHAR(30) DEFAULT '',
        msg TEXT NOT NULL,
        time VARCHAR(20) DEFAULT '',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        setting_key VARCHAR(50) PRIMARY KEY,
        setting_value TEXT DEFAULT ''
    ) ENGINE=InnoDB");

    $pdo->exec("CREATE TABLE IF NOT EXISTS equipment (
        id VARCHAR(100) PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        type VARCHAR(50) NOT NULL,
        temp DECIMAL(5,1) DEFAULT 0,
        weight DECIMAL(8,2) DEFAULT 0,
        paper INT DEFAULT 100,
        battery INT DEFAULT 100,
        fuel INT DEFAULT 100,
        status VARCHAR(50) NOT NULL DEFAULT 'Online',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    $pdo->exec("CREATE TABLE IF NOT EXISTS temperature_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        date_time VARCHAR(30) NOT NULL,
        location VARCHAR(100) NOT NULL,
        temperature DECIMAL(5,1) NOT NULL,
        staff VARCHAR(100) DEFAULT '',
        action_taken TEXT DEFAULT '',
        status VARCHAR(20) DEFAULT 'Normal',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM users");
    if ($stmt->fetch()['cnt'] == 0) {
        $pdo->exec("INSERT INTO users (name, username, password, role) VALUES
            ('Jimmy Otina', 'admin', 'admin123', 'admin'),
            ('Jane Cashier', 'cashier1', 'cash123', 'cashier'),
            ('John Supervisor', 'supervisor', 'sup123', 'supervisor')");
    }

    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM products");
    if ($stmt->fetch()['cnt'] == 0) {
        $pdo->exec("INSERT INTO products (code, name, category, unit, price, stock) VALUES
            ('P001','Pork Chops','Pork Cuts','kg',550,55),
            ('P002','Pork Ribs','Pork Cuts','kg',600,35),
            ('P003','Pork Belly','Pork Cuts','kg',450,8),
            ('P004','Pork Shoulder','Pork Cuts','kg',400,30),
            ('P005','Pork Leg (Ham)','Pork Cuts','kg',480,25),
            ('P006','Bones / Offcuts','By-Products','kg',150,10),
            ('P007','Liver','Offal','kg',300,5),
            ('P008','Intestines','Offal','kg',250,5),
            ('P009','Kidneys','Offal','kg',280,8),
            ('P010','Pork Sausages','Processed','pack',250,12),
            ('P011','Minced Meat','Processed','kg',400,18)");
    }

    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM customers");
    if ($stmt->fetch()['cnt'] == 0) {
        $pdo->exec("INSERT INTO customers (name, phone, type, area, birthday, preferred, points, spent, registered, last_visit) VALUES
            ('John Doe','0712345678','Retail','Westlands','March','Pork Chops',450,45000,'2023-09-15','Today'),
            ('Jane Smith','0798765432','Restaurant','Kilimani','July','Pork Ribs, Belly',1200,120000,'2023-08-01','Yesterday'),
            ('Mike Johnson','0723456789','Reseller','Eastleigh','January','Minced Meat',890,89000,'2023-07-20','2 days ago')");
    }

    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM batches");
    if ($stmt->fetch()['cnt'] == 0) {
        $pdo->exec("INSERT INTO batches (id, source, weight, current_weight, use_by, status, cost_per_kg, inspection, received_by, doc_ref) VALUES
            ('OIN-20231024-001','Oinklytics Farms',120,85,'2023-10-30','Active',350,'Accepted','J. Otina','INV-001'),
            ('EXT-KCMEAT-20231024-002','KC Meat Supplies',85,40,'2023-10-26','Expiring',380,'Partially Accepted','J. Smith','PO-045')");
    }

    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM equipment");
    if ($stmt->fetch()['cnt'] == 0) {
        $pdo->exec("INSERT INTO equipment (id, name, type, temp, weight, paper, battery, fuel, status) VALUES
            ('display_chiller','Display Chiller','Chiller',2.5,0,0,0,0,'Normal'),
            ('upright_chiller','Upright Chiller','Chiller',5.2,0,0,0,0,'Warning'),
            ('freezer_main','Main Freezer','Freezer',-18.5,0,0,0,0,'Normal'),
            ('scale_pos','POS Weighing Scale','Scale',0,0,0,0,0,'Online'),
            ('scale_cutting','Cutting Scale','Scale',0,0,0,0,0,'Online'),
            ('label_printer','Thermal Label Printer','Printer',0,0,85,0,0,'Online'),
            ('barcode_scanner','Barcode Scanner','Scanner',0,0,0,92,0,'Online'),
            ('pos_terminal','POS Terminal','POS',0,0,0,0,0,'Online'),
            ('generator','Backup Generator','Generator',0,0,0,0,78,'Standby')");
    }

    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM settings");
    if ($stmt->fetch()['cnt'] == 0) {
        $pdo->exec("INSERT INTO settings (setting_key, setting_value) VALUES
            ('businessName','Oinklytics Butchery'),
            ('currency','KES'),
            ('lowStockThreshold','10'),
            ('expiryWarningDays','3'),
            ('freezerMaxTemp','-15'),
            ('chillerMaxTemp','4'),
            ('earnRate','100'),
            ('pointValue','1'),
            ('minRedeem','100'),
            ('maxRedeemPct','20'),
            ('pointsExpiry','12')");
    }

    echo json_encode(['success' => true, 'message' => 'Database setup complete!']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
