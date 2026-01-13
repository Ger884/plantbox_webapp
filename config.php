<?php
// รายละเอียดการเชื่อมต่อฐานข้อมูลข้ามเครือข่าย
$db_host = "127.0.0.1";    // *** ใส่ IP ของ Raspberry Pi
$db_user = "admin";           // User ที่คุณสร้างไว้ใน MariaDB
$db_pass = "123456";   // รหัสผ่านของ User
$db_name = "plantbox";        // ชื่อฐานข้อมูลบน Raspberry Pi

// สร้างการเชื่อมต่อ
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . " (ตรวจสอบว่าเปิด bind-address ใน Pi หรือยัง)");
}

$conn->set_charset("utf8");

echo "Connected successfully to Raspberry Pi DB!";
?>