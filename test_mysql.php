<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1', 'root', '');
    echo 'MySQL is RUNNING';
} catch (Exception $e) {
    echo 'MySQL NOT running: ' . $e->getMessage();
}
