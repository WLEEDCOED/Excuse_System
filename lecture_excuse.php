<?php
session_start();
include 'db_connection.php'; // تضمين ملف الاتصال بقاعدة البيانات

// جلب المواد من جدول المواد (subjects)
$sql_subjects = "SELECT * FROM subjects";
$result_subjects = $conn->query($sql_subjects);

// جلب الأساتذة من قاعدة البيانات جلب
$sql_professors = "SELECT * FROM users WHERE role = 'professor'";
$result_professors = $conn->query($sql_professors);

// التحقق من إرسال النموذج
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject_id = $_POST['subject_id'];
    $professor_id = $_POST['professor_id'];
    $description = $_POST['description'];
    $file = $_FILES['excuse_file']['name'];

    // رفع الملف
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["excuse_file"]["name"]);
    move_uploaded_file($_FILES["excuse_file"]["tmp_name"], $target_file);

    // حفظ الطلب في قاعدة البيانات
    $sql_insert = "INSERT INTO exu (subject_id, professor_id, description, file_path, status) 
                   VALUES ('$subject_id', '$professor_id', '$description', '$target_file', 'pending')";

    if ($conn->query($sql_insert) === TRUE) {
        echo "تم تقديم العذر بنجاح وسيتم عرضه للدكتور.";
    } else {
        echo "حدث خطأ أثناء تقديم العذر: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>طلب عذر</title>
</head>
<body>
    <h2>طلب عذر</h2>
    <form action="" method="POST" enctype="multipart/form-data">
        <label for="subject">اختر المادة:</label>
        <select name="subject_id" id="subject" required>
            <option value="">اختر المادة</option>
            <?php
            if ($result_subjects->num_rows > 0) {
                while ($row = $result_subjects->fetch_assoc()) {
                    echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
                }
            }
            ?>
        </select><br><br>

        <label for="professor">اختر الدكتور:</label>
        <select name="professor_id" id="professor" required>
            <option value="">اختر الدكتور</option>
            <?php
            if ($result_professors->num_rows > 0) {
                while ($row = $result_professors->fetch_assoc()) {
                    echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
                }
            }
            ?>
        </select><br><br>

        <label for="description">وصف العذر:</label><br>
        <textarea name="description" id="description" rows="4" cols="50" required></textarea><br><br>

        <label for="excuse_file">أرفق العذر (ملف):</label>
        <input type="file" name="excuse_file" id="excuse_file" required><br><br>

        <button type="submit">إرسال العذر</button>
    </form>
</body>
</html>
