<?php
session_start();
include 'db_connection.php';

// التحقق من أن النموذج تم إرساله
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['id'];
    $password = md5($_POST['password']); // تشفير كلمة المرور باستخدام md5
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = 'student'; // تعيين دور المستخدم كطالب افتراضيًا

    // إعداد وتنفيذ الاستعلام
    $stmt = $conn->prepare("INSERT INTO users (id, password, name, email, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $password, $name, $email, $role);

    if ($stmt->execute()) {
        echo "تم إنشاء الحساب بنجاح";
    } else {
        echo "حدث خطأ: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل حساب طالب جديد</title>
    <style>
        /* نفس الأنماط السابقة */
    </style>
</head>
<body>
    <form action="register.php" method="post">
        <h2>تسجيل حساب طالب جديد</h2>
        <input type="text" name="id" placeholder="ID" required>
        <input type="password" name="password" placeholder="كلمة المرور" required>
        <input type="text" name="name" placeholder="الاسم الكامل" required>
        <input type="email" name="email" placeholder="البريد الإلكتروني" required>
        <input type="submit" value="تسجيل">
    </form>
</body>
</html>