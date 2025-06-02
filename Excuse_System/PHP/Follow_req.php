<?php
session_start();
include 'includes/db_connection.php';

// التأكد من تسجيل الدخول كطالب
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != '3') {
    header('Location: login.php');
    exit;
}

// جلب معرف الطالب من الجلسة
$student_id = $_SESSION['user_id'];

// جلب جميع الطلبات النهائية المقدمة من الطالب مع اسم المادة واسم الدكتور
$sql_final_requests = "SELECT final_exam.*, 
                          course.name AS course_name, 
                          professors.name AS professor_name 
                   FROM final_exam 
                   JOIN course ON final_exam.course_id = course.id 
                   JOIN professors ON final_exam.professor_id = professors.id 
                   WHERE final_exam.student_id = ? 
                   ORDER BY final_exam.id ASC";
$stmt_final_requests = $conn->prepare($sql_final_requests);
$stmt_final_requests->bind_param("i", $student_id);
$stmt_final_requests->execute();
$result_final_requests = $stmt_final_requests->get_result();

// جلب طلبات الأعذار اليومية من جدول 'lecture_excuses'
$sql_daily_excuses = "SELECT lecture_excuses.*, 
                             professors.name AS professor_name, 
                             course.name AS class_name
                      FROM lecture_excuses
                      JOIN professors ON lecture_excuses.professor_id = professors.id
                      JOIN course ON lecture_excuses.lectures_id = course.id
                      WHERE lecture_excuses.student_id = ?
                      ORDER BY lecture_excuses.id ASC";

$stmt_daily_excuses = $conn->prepare($sql_daily_excuses);
$stmt_daily_excuses->bind_param("i", $student_id);
$stmt_daily_excuses->execute();
$result_daily_excuses = $stmt_daily_excuses->get_result();

// جلب جميع المسؤولين من جدول 'admins'
$admins_sql = "SELECT * FROM admins";
$admins_result = $conn->query($admins_sql);

// حفظ بيانات المسؤولين
$admins = [];
while ($admin = $admins_result->fetch_assoc()) {
    $admins[$admin['id']] = $admin['name'];
}

// جلب جميع المستخدمين من جدول 'users'
$users_sql = "SELECT * FROM users";
$users_result = $conn->query($users_sql);

// حفظ بيانات المستخدمين
$users = [];
while ($user = $users_result->fetch_assoc()) {
    $users[$user['id']] = $user['name'];
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة الطالب - طلباتي</title>
   
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link href="../css/Follow_req.css" rel="stylesheet">
    <script src="../JS/req.js" defer></script>

</head>
<body>
    <button class="toggle-sidebar-btn" id="toggleSidebar"><i class="fas fa-bars"></i> </button>
    <br><br><br><br>
    <aside class="sidebar" id="sidebar">
        <div class="user-info">
        <br><br><br><br>
            <i class="fas fa-user-circle"></i>
            <div class="student-name">
                <?php echo htmlspecialchars($_SESSION['name']); ?>
            </div>
            <div class="university-id">
               <h3>الرقم الجامعي</h3>  
                <?php echo htmlspecialchars($student_id); ?>
            </div>
        </div>
        <div class="nav-menu">
            <button class="tab-link active" onclick="openTab('final-requests-container')">طلبات الأعذار النهائية</button>
            <button class="tab-link" onclick="openTab('daily-requests-container')">الطلبات اليومية</button>
        </div>
        <button class="logout-btn" onclick="window.location.href='logout.php'">تسجيل الخروج</button>
    </aside>

    <div class="dashboard-container">
        <h2>طلباتي</h2>

        <div id="final-requests-container" class="container active">
            <h3>طلبات الأعذار النهائية</h3>
            <?php
            if ($result_final_requests->num_rows > 0) {
                while ($row = $result_final_requests->fetch_assoc()) {
                    $created_at_date = date('Y-m-d', strtotime($row['created_at']));
                    $exam_date = (!empty($row['exam_date']) && $row['exam_date'] != '0000-00-00') ? date('Y-m-d', strtotime($row['exam_date'])) : 'سيتم تحديده قريبًا';

                    echo "<div class='request' id='request-" . htmlspecialchars($row['id']) . "'>";
                    echo "<h3>طلب رقم: " . htmlspecialchars($row['id']) . "</h3>";
                    echo "<p><strong>المادة:</strong> " . htmlspecialchars($row['course_name']) . "</p>";
                    echo "<p><strong>اسم الدكتور:</strong> " . htmlspecialchars($row['professor_name']) . "</p>";

                    if ($row['status'] == 'approved') {
                        echo "<p class='status-approved'>تم قبول طلبك.</p>";
                        echo "<p>موعد الاختبار هو: " . htmlspecialchars($exam_date) . "</p>";
                    } elseif ($row['status'] == 'rejected') {
                        echo "<p class='status-rejected'>تم رفض طلبك.</p>";

                        if (!empty($row['rejection_reason'])) {
                            echo "<p>سبب الرفض: " . htmlspecialchars($row['rejection_reason']) . "</p>";
                        }
                    } elseif ($row['status'] === 'Under_Progrees') {
                        echo "<p>الحالة: قيد المراجعة من مجلس الكلية</p>";
                    } else {
                        echo "<p>الحالة: " . htmlspecialchars($row['status']) . "</p>";
                    }

                    if ($row['current_level'] == 1 && isset($users[$row['current_admin']])) {
                        echo "<p>الطلب عند: " . htmlspecialchars($users[$row['current_admin']]) . "</p>";
                    } elseif ($row['current_level'] == 2 && isset($admins[$row['current_admin']])) {
                        echo "<p>الطلب عند: " . htmlspecialchars($admins[$row['current_admin']]) . "</p>";
                    }

                    echo "</div>";
                }
            } else {
                echo "<p>لا توجد طلبات حتى الآن.</p>";
            }
            ?>
        </div>

        <div id="daily-requests-container" class="container">
            <h3>الطلبات اليومية</h3>
            <?php
            if ($result_daily_excuses->num_rows > 0) {
                while ($row = $result_daily_excuses->fetch_assoc()) {
                    echo "<div class='request' id='daily-request-" . htmlspecialchars($row['id']) . "'>";
                    echo "<h3>الاعذار اليومية: " . htmlspecialchars($row['id']) . "</h3>";
                    echo "<p><strong>المادة:</strong> " . htmlspecialchars($row['class_name']) . "</p>";
                    echo "<p><strong>اسم الدكتور:</strong> " . htmlspecialchars($row['professor_name']) . "</p>";
                    echo "<p><strong>الوصف:</strong> " . htmlspecialchars($row['description']) . "</p>";
                    echo "<p><strong>تاريخ الغياب:</strong> " . htmlspecialchars($row['absence_date']) . "</p>";
                    echo "<p><strong>رقم الشعبة:</strong> " . htmlspecialchars($row['section_number']) . "</p>";
                    echo "<p><strong>حالة الطلب:</strong> " . htmlspecialchars($row['status']) . "</p>";
                    echo "</div>";
                }
            } else {
                echo "<p>لا توجد طلبات يومية حتى الآن.</p>";
            }
            ?>
        </div>

    </div>

    <footer>
        <p>&copy; 2024 بوابة تسهيل. جميع الحقوق محفوظة.</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="admin.js" defer></script>
</body>
</html>