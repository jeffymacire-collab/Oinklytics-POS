<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=oinklytics_pos', 'root', '');
$pdo->exec("INSERT INTO products (code, name, category, unit, price, status, stock) VALUES ('P001', 'Chicken Kienyeji', 'Poultry', 'kg', 600, 'Active', 0)");
echo 'Added Chicken Kienyeji at KES 600/kg';
