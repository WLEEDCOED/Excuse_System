<?php
session_start();
include 'db_connection.php';

$message = '';
$idError = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $full_name = $_POST['name'];
    $email = $_POST['email'];
    $role = 'professor';

    // تحقق من عدم وجود حقول فارغة
    if (empty($full_name)) {
        $message = "الاسم الكامل لا يمكن أن يكون فارغاً";
    } else {
        // التحقق من عدم وجود نفس القيمة للمفتاح الرئيسي بالفعل
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
        $check_stmt->bind_param("s", $id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            $idError = "هذا المعرف موجود بالفعل، يرجى استخدام معرف آخر.";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (id, password, name, email, role) VALUES (?, ?, ?, ?, ?)");
            if (!$stmt) {
                die("Error in preparing statement: " . $conn->error);
            }

            $stmt->bind_param("sssss", $id, $password, $full_name, $email, $role);

            if ($stmt->execute()) {
                $message = "تم إنشاء حساب الدكتور بنجاح";
            } else {
                $message = "حدث خطأ: " . $stmt->error;
            }

            $stmt->close();
        }

        $check_stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل حساب دكتور جديد</title>
    <style>
       
    </style>
</head>
<body>
<form action="rei.php" method="post">
        <h2>تسجيل حساب دكتور جديد</h2>
        <input type="text" name="id" placeholder="ID" required>
        <input type="password" name="password" placeholder="كلمة المرور" required>
        <input type="text" name="name" placeholder="الاسم الكامل" required>
        <input type="email" name="email" placeholder="البريد الإلكتروني" required>
        <input type="submit" value="تسجيل">
    </form>

    <!-- عرض رسالة الخطأ الخاصة بالمعرف فقط بعد إرسال النموذج -->
    <?php if (!empty($idError)): ?>
        <div class="error"><?php echo $idError; ?></div>
    <?php endif; ?>

    <!-- عرض الرسالة الأخرى فقط بعد إدخال البيانات -->
    <?php if (!empty($message)): ?>
        <div class="message <?php echo ($message === "تم إنشاء حساب الدكتور بنجاح") ? 'success' : 'error'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
</body>
</html>