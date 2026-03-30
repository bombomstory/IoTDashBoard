<?php
// Backend/db.php

$host = 'db';
$db   = 'iot';
$user = 'iot';       // แก้ไขเป็น Username ของ MySQL
$pass = 'iotP@ssw0rd';           // แก้ไขเป็น Password ของ MySQL
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // แจ้งเตือนเมื่อเกิด Error
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // ดึงข้อมูลเป็น Associative Array
    PDO::ATTR_EMULATE_PREPARES   => false,                  // ความปลอดภัยป้องกัน SQL Injection
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // ถ้าเชื่อมต่อไม่ได้ ให้ส่ง JSON Error กลับไป
    header('Content-Type: application/json');
    echo json_encode(["status" => "error", "message" => "Database connection failed."]);
    exit;
}
?>
