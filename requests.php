<?php
session_start();
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_SESSION['user_id'];
    $request_text = $_POST['request_text'];

    $sql = "INSERT INTO requests (student_id, request_text, current_admin) VALUES ($student_id, '$request_text', 1)";
    if ($conn->query($sql) === TRUE) {
        $success = "تم إرسال الطلب بنجاح.";
    } else {
        $error = "حدث خطأ أثناء إرسال الطلب.";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إرسال طلب - بوابة الطلاب</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="request-container">
        <h2>إرسال طلب جديد</h2>
        <?php if (isset($success)) { echo "<p class='success'>$success</p>"; } ?>
        <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
        <form method="POST" action="">
            <textarea name="request_text" placeholder="أدخل نص الطلب" required></textarea>
            <button type="submit">إرسال الطلب</button>
        </form>
    </div>
</body>
</html>
