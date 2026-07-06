<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once __DIR__ . '/db.php';

$entity = $_GET['entity'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true) ?? [];
$id = $_GET['id'] ?? null;

try {
    switch ($entity) {
        // ============ USERS ============
        case 'users':
            if ($method === 'GET') {
                $stmt = $pdo->query("SELECT id, name, username, role, status, last_login FROM users ORDER BY id");
                echo json_encode($stmt->fetchAll());
            } elseif ($method === 'POST') {
                $stmt = $pdo->prepare("INSERT INTO users (name, username, password, role) VALUES (?,?,?,?)");
                $stmt->execute([$input['name'], $input['username'], $input['password'], $input['role']]);
                echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
            } elseif ($method === 'PUT') {
                if (isset($input['toggleRole'])) {
                    $stmt = $pdo->prepare("UPDATE users SET role = IF(role='admin','supervisor','admin') WHERE id=? AND username!='admin'");
                    $stmt->execute([$id]);
                    echo json_encode(['success' => true]);
                }
            } elseif ($method === 'DELETE') {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id=? AND username!='admin'");
                $stmt->execute([$id]);
                echo json_encode(['success' => true]);
            }
            break;

        // ============ AUTH ============
        case 'login':
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username=? AND password=? AND role=? AND status='Active'");
            $stmt->execute([$input['username'], $input['password'], $input['role']]);
            $user = $stmt->fetch();
            if ($user) {
                $pdo->prepare("UPDATE users SET last_login='Just now' WHERE id=?")->execute([$user['id']]);
                echo json_encode(['success' => true, 'user' => $user]);
            } else {
                http_response_code(401);
                echo json_encode(['error' => 'Invalid credentials']);
            }
            break;

        // ============ PRODUCTS ============
        case 'products':
            if ($method === 'GET') {
                $stmt = $pdo->query("SELECT * FROM products ORDER BY code");
                echo json_encode($stmt->fetchAll());
            } elseif ($method === 'POST') {
                $stmt = $pdo->prepare("INSERT INTO products (code, name, category, unit, price, status, stock) VALUES (?,?,?,?,?,?,?)");
                $stmt->execute([$input['code'], $input['name'], $input['category'], $input['unit'], $input['price'], $input['status'] ?? 'Active', $input['stock'] ?? 0]);
                echo json_encode(['success' => true]);
            } elseif ($method === 'PUT') {
                $stmt = $pdo->prepare("UPDATE products SET name=?, category=?, unit=?, price=?, status=?, stock=? WHERE code=?");
                $stmt->execute([$input['name'], $input['category'], $input['unit'], $input['price'], $input['status'], $input['stock'], $id]);
                echo json_encode(['success' => true]);
            } elseif ($method === 'DELETE') {
                $stmt = $pdo->prepare("DELETE FROM products WHERE code=?");
                $stmt->execute([$id]);
                echo json_encode(['success' => true]);
            }
            break;

        // ============ CUSTOMERS ============
        case 'customers':
            if ($method === 'GET') {
                $fields = "id, name, phone, type, area, birthday, preferred, points, spent, consent, registered, last_visit AS lastVisit";
                if ($id) {
                    $stmt = $pdo->prepare("SELECT $fields FROM customers WHERE id=?");
                    $stmt->execute([$id]);
                } else {
                    $search = $_GET['search'] ?? '';
                    if ($search) {
                        $stmt = $pdo->prepare("SELECT $fields FROM customers WHERE phone LIKE ? OR name LIKE ? ORDER BY name");
                        $stmt->execute(["%$search%", "%$search%"]);
                    } else {
                        $stmt = $pdo->query("SELECT $fields FROM customers ORDER BY name");
                    }
                }
                echo json_encode($stmt->fetchAll());
            } elseif ($method === 'POST') {
                $stmt = $pdo->prepare("INSERT INTO customers (name, phone, type, area, birthday, preferred, points, spent, consent, registered, last_visit) VALUES (?,?,?,?,?,?,0,0,?,'Today','Today')");
                $stmt->execute([$input['name'], $input['phone'], $input['type'], $input['area'], $input['birthday'], $input['preferred'], $input['consent'] ?? 1]);
                echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
            } elseif ($method === 'PUT') {
                $stmt = $pdo->prepare("UPDATE customers SET name=?, phone=?, type=?, area=?, birthday=?, preferred=?, points=?, spent=?, consent=?, last_visit=? WHERE id=?");
                $stmt->execute([$input['name'], $input['phone'], $input['type'], $input['area'], $input['birthday'], $input['preferred'], $input['points'], $input['spent'], $input['consent'] ?? 1, $input['lastVisit'] ?? $input['last_visit'] ?? 'Today', $id]);
                echo json_encode(['success' => true]);
            } elseif ($method === 'DELETE') {
                $stmt = $pdo->prepare("DELETE FROM customers WHERE id=?");
                $stmt->execute([$id]);
                echo json_encode(['success' => true]);
            }
            break;

        // ============ BATCHES ============
        case 'batches':
            if ($method === 'GET') {
                $stmt = $pdo->query("SELECT id, source, weight, current_weight AS currentWeight, use_by AS useBy, status, cost_per_kg AS costPerKg, inspection, received_by AS receivedBy, doc_ref AS docRef, created_at FROM batches ORDER BY created_at DESC");
                echo json_encode($stmt->fetchAll());
            } elseif ($method === 'POST') {
                $stmt = $pdo->prepare("INSERT INTO batches (id, source, weight, current_weight, use_by, status, cost_per_kg, inspection, received_by, doc_ref) VALUES (?,?,?,?,?,?,?,?,?,?)");
                $stmt->execute([$input['id'] ?? uniqid('BATCH-'), $input['source'] ?? 'Unknown', $input['weight'] ?? 0, $input['current_weight'] ?? 0, $input['use_by'] ?? null, $input['status'] ?? 'Active', $input['cost_per_kg'] ?? 0, $input['inspection'] ?? null, $input['received_by'] ?? null, $input['doc_ref'] ?? null]);
                echo json_encode(['success' => true]);
            } elseif ($method === 'PUT') {
                $stmt = $pdo->prepare("UPDATE batches SET source=?, weight=?, current_weight=?, use_by=?, status=?, cost_per_kg=? WHERE id=?");
                $stmt->execute([$input['source'], $input['weight'], $input['currentWeight'] ?? $input['current_weight'], $input['useBy'] ?? $input['use_by'], $input['status'], $input['costPerKg'] ?? $input['cost_per_kg'], $id]);
                echo json_encode(['success' => true]);
            } elseif ($method === 'DELETE') {
                $stmt = $pdo->prepare("DELETE FROM batches WHERE id=?");
                $stmt->execute([$id]);
                echo json_encode(['success' => true]);
            }
            break;

        // ============ SALES ============
        case 'sales':
            if ($method === 'GET') {
                if ($id) {
                    $stmt = $pdo->prepare("SELECT * FROM sales WHERE id=?");
                    $stmt->execute([$id]);
                } else {
                    $stmt = $pdo->query("SELECT * FROM sales ORDER BY created_at DESC LIMIT 100");
                }
                echo json_encode($stmt->fetchAll());
            } elseif ($method === 'POST') {
                $stmt = $pdo->prepare("INSERT INTO sales (receipt_no, total, payment_method, mpesa_ref, cash_amount, mpesa_amount, customer_id, batch_id, items) VALUES (?,?,?,?,?,?,?,?,?)");
                $stmt->execute([$input['receipt_no'], $input['total'], $input['payment_method'], $input['mpesa_ref'], $input['cash_amount'], $input['mpesa_amount'], $input['customer_id'], $input['batch_id'], json_encode($input['items'])]);
                echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
            }
            break;

        // ============ AUDIT LOG ============
        case 'audit':
            if ($method === 'GET') {
                $stmt = $pdo->query("SELECT * FROM audit_log ORDER BY created_at DESC LIMIT 100");
                echo json_encode($stmt->fetchAll());
            } elseif ($method === 'POST') {
                $stmt = $pdo->prepare("INSERT INTO audit_log (time, user, role, action, description, ip) VALUES (?,?,?,?,?,?)");
                $stmt->execute([$input['time'], $input['user'], $input['role'], $input['action'], $input['description'], $input['ip']]);
                echo json_encode(['success' => true]);
            }
            break;

        // ============ NOTIFICATIONS ============
        case 'notifications':
            if ($method === 'GET') {
                $stmt = $pdo->query("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 20");
                echo json_encode($stmt->fetchAll());
            } elseif ($method === 'POST') {
                $stmt = $pdo->prepare("INSERT INTO notifications (type, icon, color, msg, time) VALUES (?,?,?,?,?)");
                $stmt->execute([$input['type'], $input['icon'], $input['color'], $input['msg'], $input['time']]);
                echo json_encode(['success' => true]);
            } elseif ($method === 'DELETE') {
                if ($id === 'all') {
                    $pdo->exec("DELETE FROM notifications");
                } else {
                    $stmt = $pdo->prepare("DELETE FROM notifications WHERE id=?");
                    $stmt->execute([$id]);
                }
                echo json_encode(['success' => true]);
            }
            break;

        // ============ SETTINGS ============
        case 'settings':
            if ($method === 'GET') {
                $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
                $result = [];
                foreach ($stmt->fetchAll() as $row) {
                    $result[$row['setting_key']] = $row['setting_value'];
                }
                echo json_encode($result);
            } elseif ($method === 'PUT') {
                foreach ($input as $key => $value) {
                    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=?");
                    $stmt->execute([$key, $value, $value]);
                }
                echo json_encode(['success' => true]);
            }
            break;

        // ============ DASHBOARD STATS ============
        case 'dashboard':
            $today = date('Y-m-d');
            $stmt = $pdo->query("SELECT COUNT(*) as cnt, COALESCE(SUM(total),0) as total FROM sales WHERE DATE(created_at) = '$today'");
            $salesToday = $stmt->fetch();
            $stmt = $pdo->query("SELECT COALESCE(SUM(cash_amount),0) as cash, COALESCE(SUM(mpesa_amount),0) as mpesa FROM sales WHERE DATE(created_at) = '$today'");
            $paymentSplit = $stmt->fetch();
            $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM batches WHERE status='Active'");
            $activeBatches = $stmt->fetch()['cnt'];
            $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM customers");
            $totalCustomers = $stmt->fetch()['cnt'];
            $stmt = $pdo->query("SELECT COALESCE(SUM(qty),0) as qty FROM wastage WHERE DATE(created_at) = '$today'");
            $wastageToday = $stmt->fetch()['qty'];
            echo json_encode([
                'todaySales' => $salesToday['total'],
                'cashSales' => $paymentSplit['cash'],
                'mpesaSales' => $paymentSplit['mpesa'],
                'transactions' => $salesToday['cnt'],
                'activeBatches' => $activeBatches,
                'totalCustomers' => $totalCustomers,
                'wastageToday' => $wastageToday
            ]);
            break;

        // ============ CATEGORIES ============
        case 'categories':
            if ($method === 'GET') {
                $stmt = $pdo->query("SELECT * FROM categories ORDER BY id");
                echo json_encode($stmt->fetchAll());
            } elseif ($method === 'POST') {
                $stmt = $pdo->prepare("INSERT IGNORE INTO categories (name) VALUES (?)");
                $stmt->execute([$input['name']]);
                if ($stmt->rowCount() === 0) {
                    http_response_code(409);
                    echo json_encode(['error' => 'Category already exists']);
                } else {
                    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
                }
            } elseif ($method === 'PUT') {
                $stmt = $pdo->prepare("UPDATE categories SET name=? WHERE id=?");
                $stmt->execute([$input['name'], $id]);
                echo json_encode(['success' => true]);
            } elseif ($method === 'DELETE') {
                $stmt = $pdo->prepare("DELETE FROM categories WHERE id=?");
                $stmt->execute([$id]);
                echo json_encode(['success' => true]);
            }
            break;

        // ============ EQUIPMENT ============
        case 'equipment':
            if ($method === 'GET') {
                $stmt = $pdo->query("SELECT * FROM equipment ORDER BY id");
                echo json_encode($stmt->fetchAll());
            } elseif ($method === 'POST') {
                $stmt = $pdo->prepare("INSERT INTO equipment (id, name, type, temp, weight, paper, battery, fuel, status) VALUES (?,?,?,?,?,?,?,?,?)");
                $stmt->execute([$input['id'], $input['name'], $input['type'], $input['temp'] ?? 0, $input['weight'] ?? 0, $input['paper'] ?? 100, $input['battery'] ?? 100, $input['fuel'] ?? 100, $input['status'] ?? 'Online']);
                echo json_encode(['success' => true]);
            } elseif ($method === 'PUT') {
                $stmt = $pdo->prepare("UPDATE equipment SET name=?, type=?, temp=?, weight=?, paper=?, battery=?, fuel=?, status=? WHERE id=?");
                $stmt->execute([$input['name'], $input['type'], $input['temp'] ?? 0, $input['weight'] ?? 0, $input['paper'] ?? 100, $input['battery'] ?? 100, $input['fuel'] ?? 100, $input['status'], $id]);
                echo json_encode(['success' => true]);
            } elseif ($method === 'DELETE') {
                $stmt = $pdo->prepare("DELETE FROM equipment WHERE id=?");
                $stmt->execute([$id]);
                echo json_encode(['success' => true]);
            }
            break;

        // ============ VERIFY ADMIN PIN ============
        case 'verify_pin':
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username='admin' AND password=? AND role='admin'");
            $stmt->execute([$input['password']]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Invalid admin PIN']);
            }
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid entity']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
