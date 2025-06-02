<?php
session_start();
include 'includes/db_connection.php.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != '3') {
    header('Location: login.php');
    exit;
}

$student_id = $_SESSION['user_id']; // معرف الطالب المسجل

// جلب السنوات الدراسية من جدول `academic_years`
$sql_academic_years = "SELECT id, year FROM academic_years";
$result_academic_years = $conn->query($sql_academic_years);
$academic_years = [];
if ($result_academic_years->num_rows > 0) {
    while ($year = $result_academic_years->fetch_assoc()) {
        $academic_years[] = $year;
    }
}

// معالجة إرسال النموذج
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $response = [];

    // استرجاع بيانات الطلب
    $academic_year_id = $_POST['academic_year_id'];
    $semester_id = $_POST['semester_id'];
    $course_id = $_POST['course_id'];
    $absence_date = $_POST['absence_date'];
    $description = $_POST['description'];
    $excuse = $_POST['excuse'];

    // رفع الملف
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

    // فحص نوع الملف باستخدام Fileinfo
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $_FILES["excuse_file"]["tmp_name"]);
    finfo_close($finfo);
    $allowed_mimes = array("application/pdf", "application/msword", "application/vnd.openxmlformats-officedocument.wordprocessingml.document");
    if (!in_array($mime, $allowed_mimes)) {
        $response['error'] = "عذراً، نوع الملف غير مسموح به.";
        $uploadOk = 0;
    }

    if ($uploadOk == 0) {
        $response['error'] = isset($response['error']) ? $response['error'] : "عذراً، لم يتم رفع الملف.";
    } else {
        // إنشاء اسم ملف فريد
        $new_file_name = uniqid('file_', true) . '.' . $fileType;
        $target_file = $target_dir . $new_file_name;

        if (move_uploaded_file($_FILES["excuse_file"]["tmp_name"], $target_file)) {
            // جلب قسم الطالب
            $sql_student = "SELECT department_id FROM students WHERE id = ?";
            $stmt_student = $conn->prepare($sql_student);
            if (!$stmt_student) {
                $response['error'] = "خطأ في تحضير الاستعلام: " . $conn->error;
                echo json_encode($response);
                exit;
            }
            $stmt_student->bind_param("i", $student_id);
            $stmt_student->execute();
            $result_student = $stmt_student->get_result();

            if ($result_student->num_rows > 0) {
                $student = $result_student->fetch_assoc();
                $student_department_id = $student['department_id'];
                error_log("Student Department ID: " . $student_department_id);
            } else {
                $response['error'] = "تعذر العثور على بيانات الطالب.";
                error_log("Error: " . $response['error']);
                echo json_encode($response);
                exit;
            }

            // جلب المسؤول في نفس القسم
            $sql_admin = "SELECT id FROM users WHERE department_id = ? AND role_id = 1 LIMIT 1";
            $stmt_admin = $conn->prepare($sql_admin);
            if (!$stmt_admin) {
                $response['error'] = "خطأ في تحضير الاستعلام: " . $conn->error;
                error_log("Error: " . $response['error']);
                echo json_encode($response);
                exit;
            }
            $stmt_admin->bind_param("i", $student_department_id);
            $stmt_admin->execute();
            $result_admin = $stmt_admin->get_result();

            if ($result_admin->num_rows > 0) {
                $admin = $result_admin->fetch_assoc();
                $current_admin_id = $admin['id'];
                $current_admin_table = 'users';
                error_log("Admin ID: " . $current_admin_id);
            } else {
                $response['error'] = "لا يوجد مسؤول في نفس القسم.";
                error_log("Error: " . $response['error']);
                echo json_encode($response);
                exit;
            }

            // جلب `professor_id` المرتبط بالمادة
            $sql_professor = "SELECT professor_id FROM course_professor WHERE course_id = ? LIMIT 1";
            $stmt_professor = $conn->prepare($sql_professor);
            if (!$stmt_professor) {
                $response['error'] = "خطأ في تحضير الاستعلام: " . $conn->error;
                error_log("Error: " . $response['error']);
                echo json_encode($response);
                exit;
            }
            $stmt_professor->bind_param("i", $course_id);
            $stmt_professor->execute();
            $result_professor = $stmt_professor->get_result();

            if ($result_professor->num_rows > 0) {
                $professor = $result_professor->fetch_assoc();
                $professor_id = $professor['professor_id'];
                error_log("Professor ID: " . $professor_id);
            } else {
                $response['error'] = "تعذر العثور على الدكتور المرتبط بالمادة.";
                error_log("Error: " . $response['error']);
                echo json_encode($response);
                exit;
            }

            // إدخال الطلب في قاعدة البيانات مع `professor_id`
            $sql = "INSERT INTO final_exam (student_id, academic_year_id, semester_id, course_id, absence_date, description, excuse, file_path, professor_id, status, current_admin, current_admin_table, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?, ?, CURRENT_TIMESTAMP)";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                $response['error'] = "خطأ في تحضير الاستعلام: " . $conn->error;
                error_log("Error: " . $response['error']);
                echo json_encode($response);
                exit;
            }
            // سلسلة الأنواع: i = int, s = string
            $stmt->bind_param("iiiissssiis", $student_id, $academic_year_id, $semester_id, $course_id, $absence_date, $description, $excuse, $target_file, $professor_id, $current_admin_id, $current_admin_table);

            if ($stmt->execute()) {
                $response['success'] = "تم إرسال طلبك بنجاح.";
                // إضافة تصحيح
                error_log("Request ID: " . $stmt->insert_id . " inserted successfully.");
            } else {
                $response['error'] = "خطأ: " . $stmt->error;
                // إضافة تصحيح
                error_log("Insert Error: " . $stmt->error);
            }
        } else {
            $response['error'] = "عذراً، حدث خطأ أثناء رفع الملف.";
            error_log("Error: " . $response['error']);
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
    <title>إرسال الامتحان النهائي - بوابة الطلاب</title>
    <!-- إضافة خط من Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
   
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link href="../css/Final_exam.css" rel="stylesheet">
    <script src="../JS/Final_exam.js" defer></script>
</head>
<body>

    <!-- الهيدر (Header) -->
    <nav class="navbar">
        <div class="nav-logo">بوابة الطلاب</div>
        <button class="logout-btn" onclick="window.location.href='logout.php'">تسجيل الخروج</button>
    </nav>

    <!-- محتوى الصفحة -->
    <div class="content-wrapper">
        <div class="form-container">
            <!-- أيقونة الإغلاق -->
            <a href="HomePage.php">
                <img src="Image/letter-x.png" alt="إغلاق" class="close-icon">
            </a>

            <!-- أيقونة الطالب -->
            <img src="Image/male-student.png" alt="أيقونة الطالب" class="student-icon">

            <h2>إرسال العذر للاختبار النهائي</h2>

            <!-- عرض رسالة النجاح أو الخطأ -->
            <div id="message"></div>

            <!-- نموذج إرسال الطلب -->
            <form id="requestForm" method="post" enctype="multipart/form-data">
                <label for="academic_year">اختر السنة الدراسية:</label>
                <select name="academic_year_id" id="academic_year" required>
                    <option value="">اختر السنة الدراسية</option>
                    <?php
                    if ($academic_years) {
                        foreach ($academic_years as $year) {
                            echo "<option value='" . htmlspecialchars($year['id']) . "'>" . htmlspecialchars($year['year']) . "</option>";
                        }
                    }
                    ?>
                </select>

                <label for="semester">اختر الفصل الدراسي:</label>
                <select name="semester_id" id="semester" required>
                    <option value="">اختر الفصل الدراسي</option>
                    <!-- سيتم تعبئتها بواسطة AJAX -->
                </select>

                <label for="course">اختر المادة:</label>
                <select name="course_id" id="course" required>
                    <option value="">اختر المادة</option>
                    <!-- سيتم تعبئتها بواسطة AJAX -->
                </select>

                <label for="absence_date">تاريخ الغياب:</label>
                <input type="date" name="absence_date" id="absence_date" required>

                <label for="excuse"> العذر:</label>
                <input type="text" name="excuse" id="excuse" required>

                <label for="description">وصف العذر:</label>
                <textarea name="description" id="description" required></textarea>

                <label for="excuse_file">أرفق العذر (ملف):</label>
                <input type="file" name="excuse_file" id="excuse_file" required>

                <button type="submit">إرسال</button>
            </form>
        </div>
    </div>

    <!-- الفوتر (Footer) -->
    <footer>
        <p>&copy; 2024 بوابة تسهيل. جميع الحقوق محفوظة.</p>
    </footer>

</body>
</html>
