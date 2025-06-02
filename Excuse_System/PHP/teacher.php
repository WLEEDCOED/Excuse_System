<?php
session_start();
include 'includes/db_connection.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != '2') {
    header('Location: PHP/login.php');

    exit;
}

$professor_name = $_SESSION['name']; 


$sql_requests = "SELECT lecture_excuses.id, students.name AS student_name, students.id AS university_id, course.name AS subject_name, 
                        lecture_excuses.description, lecture_excuses.file_path, lecture_excuses.status, lecture_excuses.created_at, 
                        lecture_excuses.section_number, lecture_excuses.absence_date AS absence_date
                 FROM lecture_excuses
                 JOIN students ON lecture_excuses.student_id = students.id
                 JOIN course ON lecture_excuses.lectures_id = course.id
                 WHERE lecture_excuses.professor_id = ? AND lecture_excuses.status = 'pending'";

$stmt = $conn->prepare($sql_requests);
if ($stmt === false) {
    die("Error in SQL: " . $conn->error); 
}
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result_requests = $stmt->get_result();

// معالجة قبول أو رفض الطلبات
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['request_id'])) {
    $action = $_POST['action'];
    $request_id = $_POST['request_id'];
    
    $status = ($action === 'approved') ? 'approved' : 'rejected';

    // تحديث حالة الطلب في قاعدة البيانات
    $sql_update = "UPDATE lecture_excuses SET status = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("si", $status, $request_id);

    if ($stmt_update->execute()) {
        // إرسال رد ناجح مع الحالة
        echo json_encode(['success' => true, 'message' => 'تم تحديث الطلب بنجاح.', 'status' => $status]);
    } else {
        // في حال حدوث خطأ
        echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء تحديث الطلب.']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>الطلبات المقدمة</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <link href="../css/teacher.css" rel="stylesheet">
 
</head>
<body>

 
    <div class="header">

        <div class="professor-name">مرحباً، دكتور <?php echo htmlspecialchars($professor_name); ?></div>
        <nav class="nav">
            <a href="logout.php" class="logout-btn">تسجيل الخروج</a>
        </nav>
    </div>


    <h2>الطلبات المقدمة</h2>

   
    <div class="container">
        <?php
        if ($result_requests->num_rows > 0) {
            while ($row = $result_requests->fetch_assoc()) {
                echo "<div class='request' id='row-{$row['id']}'>";
                echo "<div class='request-header'>طلب الغياب</div>";
                echo "<div class='request-info'>";
                echo "<h3>اسم الطالب: " . htmlspecialchars($row['student_name']) . " (الرقم الجامعي: " . htmlspecialchars($row['university_id']) . ")</h3>";
                echo "<h3>المادة: " . htmlspecialchars($row['subject_name']) . "</h3>";
                echo "<p>الوصف: " . htmlspecialchars($row['description']) . "</p>";
                echo "<p><a href='" . htmlspecialchars($row['file_path']) . "' target='_blank'>عرض الملف</a></p>";
                echo "<p>تاريخ الغياب: " . htmlspecialchars($row['absence_date']) . "</p>";
                echo "<p>رقم الشعبة: " . htmlspecialchars($row['section_number']) . "</p>";
                echo "</div>";
                echo "<div class='actions'>";
                echo "<button class='btn btn-accept' data-id='" . $row['id'] . "'>قبول</button>";
                echo "<button class='btn btn-reject' data-id='" . $row['id'] . "'>رفض</button>";
                echo "</div>";
                echo "</div>";
            }
        } else {
            echo "<div class='empty-message'>لا توجد طلبات بعد.</div>";
        }
        ?>
    </div>

   
    <footer>
        <p>&copy; 2024 بوابة تستهيل. جميع الحقوق محفوظة.</p>
       
    </footer>

    <!--jQuery-->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
   
   <script src="../JS/teacher.js" defer></script>
   
</body>
</html>
