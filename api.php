<?php
// อนุญาตให้เข้าถึงจาก Domain อื่น (CORS) เพื่อป้องกันปัญหา "โหนดออฟไลน์"
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

// รายละเอียดการเชื่อมต่อฐานข้อมูล
$host = "localhost";
$user = "root";
$pass = "your_password"; // <--- แก้ไขรหัสผ่าน MariaDB ของคุณที่นี่
$db   = "plantbox";

$conn = new mysqli($host, $user, $pass, $db);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]);
    exit();
}

// ตั้งค่าภาษาไทยให้รองรับ
$conn->set_charset("utf8");

// ตรวจสอบว่าหน้าเว็บขอ "ประวัติ" (history) หรือ "ค่าล่าสุด" (live)
if (isset($_GET['history'])) {
    // ดึง 20 ข้อมูลล่าสุดมาทำกราฟ
    $sql = "SELECT temp, hum, ec, ph, n, p, k, timestamp as created_at 
            FROM soil_data 
            ORDER BY timestamp DESC LIMIT 20";
} else {
    // ดึงค่าล่าสุดเพียง 1 ค่า
    $sql = "SELECT temp, hum, ec, ph, n, p, k, timestamp as created_at 
            FROM soil_data 
            ORDER BY timestamp DESC LIMIT 1";
}

$result = $conn->query($sql);
$data = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // แปลงตัวเลขให้เป็นชนิดข้อมูลที่ถูกต้อง (float/int)
        $row['temp'] = (float)$row['temp'];
        $row['hum']  = (float)$row['hum'];
        $row['ec']   = (int)$row['ec'];
        $row['ph']   = (float)$row['ph'];
        $row['n']    = (int)$row['n'];
        $row['p']    = (int)$row['p'];
        $row['k']    = (int)$row['k'];
        $data[] = $row;
    }
    echo json_encode($data);
} else {
    echo json_encode([]); // ส่งอาร์เรย์ว่างกลับไปถ้าไม่มีข้อมูล
}

$conn->close();
?>