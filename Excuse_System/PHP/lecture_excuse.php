<?php
session_start();
include 'includes/db_connection.php';

// التأكد من تسجيل الدخول كطالب
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != '3') {
    header('Location: login.php');
    exit;
}

$student_id = $_SESSION['user_id']; // هذا يضمن استخدام معرف الطالب المسجل

// جلب السنوات الدراسية من جدول `academic_years`
$sql_academic_year = "SELECT * FROM academic_years";
$result_academic_year = $conn->query($sql_academic_year);

// معالجة إرسال النموذج
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $response = [];

    // استرجاع بيانات الطلب
    $academic_year_id = $_POST['academic_year_id'];
    $semester_id = $_POST['semester_id'];
    $lectures_id = $_POST['subject_id'];
    $description = $_POST['description'];
    $section_number = $_POST['section_number'];
    $absence_date = $_POST['absence_date'];

    $target_dir = "uploads/";
    $file_name = basename($_FILES["excuse_file"]["name"]);
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    // تحقق من حجم الملف (أقل من 5 ميجابايت)
    if ($_FILES["excuse_file"]["size"] > 5000000) {
        $response['error'] = "عذراً، حجم الملف كبير جداً.";
        $uploadOk = 0;
    }

    // السماح بأنواع معينة فقط من الملفات
    $allowed_types = array("pdf", "doc", "docx");
    if (!in_array($fileType, $allowed_types)) {
        $response['error'] = "عذراً، فقط الملفات من نوع PDF, DOC, DOCX مسموح بها.";
        $uploadOk = 0;
    }

    if ($uploadOk == 0) {
        $response['error'] = "عذراً، لم يتم رفع الملف.";
    } else {
        $new_file_name = uniqid('file_', true) . '.' . $fileType;
        $target_file = $target_dir . $new_file_name;

        if (move_uploaded_file($_FILES["excuse_file"]["tmp_name"], $target_file)) {
            // جلب الدكتور المرتبط بالمادة
            $sql_professor = "SELECT professor_id FROM course_professor WHERE course_id = ?";
            $stmt_professor = $conn->prepare($sql_professor);
            $stmt_professor->bind_param("i", $lectures_id);
            $stmt_professor->execute();
            $result_professor = $stmt_professor->get_result();

            if ($result_professor->num_rows > 0) {
                $professor = $result_professor->fetch_assoc();
                $professor_id = $professor['professor_id'];
            } else {
                $response['error'] = "تعذر العثور على الدكتور المرتبط بالمادة.";
                echo json_encode($response);
                exit;
            }

            // إدخال البيانات في قاعدة البيانات
            $sql = "INSERT INTO lecture_excuses (academic_year_id, semester_id, lectures_id, professor_id, student_id, description, file_path, status, absence_date, section_number)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiiiissss", $academic_year_id, $semester_id, $lectures_id, $professor_id, $student_id, $description, $target_file, $absence_date, $section_number);

            if ($stmt->execute()) {
                $response['success'] = "تم تقديم العذر بنجاح وسيتم مراجعته.";
            } else {
                $response['error'] = "خطأ: " . $stmt->error;
            }
        } else {
            $response['error'] = "عذراً، حدث خطأ أثناء رفع الملف.";
        }
    }

    echo json_encode($response);
    exit;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>طلب عذر - بوابة الطلاب</title>
 
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
   
    <link href="../css/lecture_excuse.css" rel="stylesheet">
  
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    
    <script src="../JS/lecture_excuse.js" defer></script>

</head>
<body>
<nav class="navbar">
        <div class="nav-logo">بوابة الطلاب</div>
        <button class="logout-btn" onclick="window.location.href='logout.php'">تسجيل الخروج</button>
    </nav>

    <div class="content-wrapper">
        <div class="form-container">
            <a href="HomePage.php">
                <img src="Image/letter-x.png" alt="إغلاق" class="close-icon">
            </a>
            <img src="Image/male-student.png" alt="أيقونة الطالب" class="student-icon">
            <h2>عذر الغياب عن المحاضرات</h2>

            <div id="message"></div>

            <form id="requestForm" method="post" enctype="multipart/form-data">
                <label for="academic_year">اختر السنة الدراسية:</label>
                <select name="academic_year_id" id="academic_year" required>
                    <option value="">اختر السنة الدراسية</option>
                    <?php
                    if ($result_academic_year->num_rows > 0) {
                        while ($row = $result_academic_year->fetch_assoc()) {
                            echo "<option value='" . htmlspecialchars($row['id']) . "'>" . htmlspecialchars($row['year']) . "</option>";
                        }
                    }
                    ?>
                </select>

                <label for="semester">اختر الفصل الدراسي:</label>
                <select name="semester_id" id="semester" required>
                    <option value="">اختر الفصل الدراسي</option>
                    <?php
                    if ($result_semesters->num_rows > 0) {
                        while ($row = $result_semesters->fetch_assoc()) {
                            echo "<option value='" . htmlspecialchars($row['id']) . "'>" . htmlspecialchars($row['name']) . "</option>";
                        }
                    }
                    ?>
                </select>

                <label for="subject">اختر المادة:</label>
                <select name="subject_id" id="subject" required>
                    <option value="">اختر المادة</option>
                    <?php
                    if ($result_courses->num_rows > 0) {
                        while ($row = $result_courses->fetch_assoc()) {
                            echo "<option value='" . htmlspecialchars($row['id']) . "'>" . htmlspecialchars($row['name']) . "</option>";
                        }
                    }
                    ?>
                </select>

                <label for="description">وصف العذر:</label>
                <textarea name="description" id="description" required></textarea>

                <label for="excuse_file">أرفق العذر (ملف):</label>
                <input type="file" name="excuse_file" id="excuse_file" required>

                <label for="section_number">رقم الشعبة:</label>
                <input type="text" name="section_number" id="section_number" required>

                <label for="absence_date">تاريخ الغياب:</label>
                <input type="date" name="absence_date" id="absence_date" required>

                <button type="submit">إرسال العذر</button>
            </form>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 بوابة تسهيل. جميع الحقوق محفوظة.</p>
    </footer>
</body>
</html>