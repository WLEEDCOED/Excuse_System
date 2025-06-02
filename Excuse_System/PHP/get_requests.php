<?php
include 'includes/db_connection.php'; 
session_start();

// التحقق من تسجيل الدخول كمسؤول
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// جلب معرف المسؤول الحالي وجدوله
$current_admin_id = $_SESSION['user_id'];

if ($_SESSION['table'] == 'admins') {
    $current_admin_table = 'admins';
    $sql_admin = "SELECT name FROM admins WHERE id = ?";
    $stmt_admin = $conn->prepare($sql_admin);
    $stmt_admin->bind_param("i", $current_admin_id);
    $stmt_admin->execute();
    $result_admin = $stmt_admin->get_result();

    if ($result_admin && $result_admin->num_rows > 0) {
        $admin_row = $result_admin->fetch_assoc();
        $current_admin_name = $admin_row['name'];
    } else {
        echo "لم يتم العثور على المسؤول الحالي.";
        exit();
    }
} else {
    $current_admin_table = 'users';
    $sql_user = "SELECT name, department_id FROM users WHERE id = ?";
    $stmt_user = $conn->prepare($sql_user);
    $stmt_user->bind_param("i", $current_admin_id);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();

    if ($result_user && $result_user->num_rows > 0) {
        $admin_row = $result_user->fetch_assoc();
        $current_admin_name = $admin_row['name'];
        $current_admin_department_id = $admin_row['department_id'];
    } else {
        echo "لم يتم العثور على المسؤول الحالي.";
        exit();
    }
}

// تعديل استعلام SQL لإضافة اسم المادة واسم الدكتور واسم القسم الدراسي
if ($current_admin_table == 'users') {
    $sql_requests = "SELECT final_exam.*, students.name AS student_name, department.name AS department_name,
        course.name AS course_name, professors.name AS professor_name
        FROM final_exam
        JOIN students ON final_exam.student_id = students.id
        LEFT JOIN department ON students.department_id = department.id
        LEFT JOIN course ON final_exam.course_id = course.id
        LEFT JOIN professors ON final_exam.professor_id = professors.id
        WHERE final_exam.status NOT IN ('Approved', 'Rejected')
        AND final_exam.current_admin = ?
        AND final_exam.current_admin_table = ?
        AND students.department_id = ?";
    $stmt_requests = $conn->prepare($sql_requests);
    if (!$stmt_requests) {
        die("خطأ في تحضير الاستعلام: " . $conn->error);
    }
    $stmt_requests->bind_param("isi", $current_admin_id, $current_admin_table, $current_admin_department_id);
} else {
    // الكود للمسؤولين من جدول admins
    $sql_requests = "SELECT final_exam.*, students.name AS student_name, department.name AS department_name,
        course.name AS course_name, professors.name AS professor_name
        FROM final_exam
        JOIN students ON final_exam.student_id = students.id
        LEFT JOIN department ON students.department_id = department.id
        LEFT JOIN course ON final_exam.course_id = course.id
        LEFT JOIN professors ON final_exam.professor_id = professors.id
        WHERE final_exam.status NOT IN ('Approved', 'Rejected')
        AND final_exam.current_admin = ?
        AND final_exam.current_admin_table = ?";
    $stmt_requests = $conn->prepare($sql_requests);
    if (!$stmt_requests) {
        die("خطأ في تحضير الاستعلام: " . $conn->error);
    }
    $stmt_requests->bind_param("is", $current_admin_id, $current_admin_table);
}

if (!$stmt_requests->execute()) {
    die("خطأ في تنفيذ الاستعلام: " . $stmt_requests->error);
}

$result = $stmt_requests->get_result();
if (!$result) {
    die("خطأ في الحصول على النتائج: " . $stmt_requests->error);
}
?>


<div >
    <h2>إدارة الطلبات</h2>
    <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    // تنسيق التاريخ فقط بدون ساعة
                    $created_at_date = date('Y-m-d', strtotime($row['created_at']));
                    $absence_date = date('Y-m-d', strtotime($row['absence_date']));

                    echo "<div id='request-" . $row['id'] . "' class='request'>";
                    
                    // قسم معلومات الطلب
                    echo "<div class='request-info'>";
                    echo "<span><strong>اسم الطالب:</strong> " . htmlspecialchars($row['student_name']) . "</span>";
                    echo "<span><strong>رقم الجامعي:</strong> " . htmlspecialchars($row['student_id']) . "</span>";
                    echo "<span><strong>رقم الطلب:</strong> " . htmlspecialchars($row['id']) . "</span>";
                    echo "<span><strong>تاريخ الطلب:</strong> " . htmlspecialchars($created_at_date) . "</span>";
                    
                    // عرض قسم الطالب فقط إذا كان موجودًا
                    if (isset($row['department_name']) && !empty($row['department_name'])) {
                        echo "<span><strong>قسم الطالب:</strong> " . htmlspecialchars($row['department_name']) . "</span>";
                    }
                    
                    echo "<button class='btn btn-display' onclick='toggleDetails(" . $row['id'] . ")'>عرض العذر</button>";
                    echo "</div>"; // نهاية div الخاص بـ request-info

                    // قسم التفاصيل
                    echo "<div id='details-" . $row['id'] . "' class='details'>";
                    echo "<p><strong>المادة:</strong> " . htmlspecialchars($row['course_name']) . "</p>";
                    echo "<p><strong>اسم الدكتور:</strong> " . htmlspecialchars($row['professor_name']) . "</p>";
                    echo "<p><strong>الوصف:</strong> " . htmlspecialchars($row['description']) . "</p>";
                    echo "<p><strong>العذر:</strong> " . htmlspecialchars($row['excuse']) . "</p>";
                    echo "<p><strong>تاريخ الغياب:</strong> " . htmlspecialchars($absence_date) . "</p>";

                    if ($row['file_path']) {
                        echo "<p><strong>ملف مرفق:</strong> <a href='view_file.php?file=" . urlencode($row['file_path']) . "' target='_blank'>عرض الملف</a></p>";
                    }

                    // قسم الأزرار
                    echo "<div class='actions'>";
                    
                    // أزرار الموافقة والرفض
                    if ($current_admin_id != 3) {
                        echo "<button type='button' class='btn btn-accept' onclick='handleApproval(" . $row['id'] . ")'>قبول</button>";
                        echo "<button type='button' class='btn btn-reject' onclick='handleRejection(" . $row['id'] . ")'>رفض</button>";
                    }

                    // زر إرسال إلى مجلس الكلية
                    if ($current_admin_table == 'admins' && $current_admin_id == 2) {
                        echo "<div class='send-to-council-box'>";
                        echo "<button type='button' class='btn btn-send-to-council' onclick='handleSendToCouncil(" . $row['id'] . ")'>إرسال إلى مجلس الكلية</button>";
                        echo "</div>";
                    }
                    
                    // زر تحديد تاريخ الامتحان
                    if ($current_admin_id == 3) {
                        echo "<button type='button' class='btn btn-accept' onclick='setExamDate(" . $row['id'] . ")'>تحديد تاريخ الامتحان</button>";
                    }

                    echo "</div>"; // نهاية div الخاص بالأزرار
                    echo "</div>"; // نهاية div الخاص بالتفاصيل
                    echo "</div>"; // نهاية div الخاص بالطلب
                }
            } else {
                echo "<p style='color: black; text-align: center;'>لا توجد طلبات للمراجعة في الوقت الحالي.</p>";
            }
            ?>