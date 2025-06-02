<?php
session_start();
include 'db_connection.php'; 

// Checking if the user is logged in
if (isset($_SESSION['user_id'])) {
    $student_id = $_SESSION['user_id'];
} else {
    // Redirect to login page if the user is not logged in
    header('Location: login.php');
    exit;
}

// Fetching student details from the 'students' table using the student ID stored in the session
$sql_student = "SELECT name, id AS university_id, department_id FROM students WHERE id = ?";
$stmt_student = $conn->prepare($sql_student);
$stmt_student->bind_param("i", $student_id);
$stmt_student->execute();
$result_student = $stmt_student->get_result();

if ($result_student->num_rows > 0) {
    // Fetch the student data
    $student = $result_student->fetch_assoc();
    $student_name = $student['name'];
    $university_id = $student['university_id'];
    
    // Fetching the department name based on department_id from the 'departments' table
    $sql_department = "SELECT name FROM department WHERE id = ?";
    $stmt_department = $conn->prepare($sql_department);
    $stmt_department->bind_param("i", $student['department_id']);
    $stmt_department->execute();
    $result_department = $stmt_department->get_result();

    if ($result_department->num_rows > 0) {
        // Fetch department name
        $department = $result_department->fetch_assoc();
        $department_name = $department['name'];
    } else {
        $department_name = "غير معروف"; // In case the department is not found
    }
} else {
    // Redirecting to login if student data is not found
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم الطالب</title>
  
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <link href="../css/Homepage.css" rel="stylesheet">

</head>
<body>

 
<nav class="navbar">
    <div class="student-info">
        <?php
        // Display student name, university ID, and department name in the header
        if (isset($student_name) && isset($university_id) && isset($department_name)) {
            echo "<span> مرحباً، " . htmlspecialchars($student_name) . "</span>";
            echo "<span> | رقم الجامعي: " . htmlspecialchars($university_id) . "</span>";
            echo "<span> | القسم: " . htmlspecialchars($department_name) . "</span>";
        } else {
            echo "<span> مرحباً بالطالب</span>";
        }
        ?>
    </div>
    <button class="logout-btn" onclick="window.location.href='logout.php'">تسجيل الخروج</button>
</nav>

   
    <div class="content-wrapper">
      
        <div class="dashboard-container">
            <h1 class="dashboard-title">اختر العذر المناسب لإرسال طلب</h1>
            <div class="buttons-grid">
                <a href="Follow_req.php" class="square-btn">تتبع الطلب</a>
                <a href="exam_final.php" class="square-btn">عذر الاختبار النهائي</a>
                <a href="lecture_excuse.php" class="square-btn">أعذار المحاضرات اليومية</a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 بوابة تسهيل. جميع الحقوق محفوظة.</p>
    </footer>
</body>
</html>
