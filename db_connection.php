<?php
$servername = "localhost";
$username = "root"; // تغيير حسب الإعدادات الخاصة بك
$password = ""; // تغيير حسب الإعدادات الخاصة بك
$dbname = "student_portal";

// إنشاء اتصال
$conn = new mysqli($servername, $username, $password, $dbname);

// التحقق من الاتصال
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
