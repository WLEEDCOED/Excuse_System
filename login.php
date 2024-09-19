<?php
session_start();
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // جلب المستخدم من قاعدة البيانات
    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // التحقق من كلمة المرور باستخدام password_verify و md5
        $passwordCorrect = password_verify($password, $user['password']) || md5($password) == $user['password'];

        if ($passwordCorrect) {
            // تخزين معلومات المستخدم في الجلسة
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];

            // إعادة التوجيه بناءً على دور المستخدم
            if ($user['role'] == 'student') {
                header("Location: Home.php"); // صفحة الطالب
                exit;
            } elseif ($user['role'] == 'admin') {
                header("Location: admin_dashboard.php"); // صفحة المسؤول
                exit;
            } elseif ($user['role'] == 'professor') {
                header("Location: prof.php"); // صفحة دكتور
                exit;
            } else {
                $error = "الدور غير معروف.";
            }
        } else {
            $error = "كلمة المرور غير صحيحة.";
        }
    } else {
        $error = "المستخدم غير موجود.";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - بوابة الطلاب</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="login-container">
        <h2>تسجيل الدخول</h2>
        <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
        <form method="POST" action="">
            <input type="email" name="email" placeholder="البريد الإلكتروني" required>
            <input type="password" name="password" placeholder="كلمة المرور" required>
            <button type="submit">تسجيل الدخول</button>
        </form>
    </div>
</body>
</html>
