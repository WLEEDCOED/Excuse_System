<?php
session_start();
include 'includes/db_connection.php'; 

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: PHP/login.php");
    exit();
}

// جلب معرف المستخدم الحالي
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

// معالجة طلبات POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $request_id = $_POST['request_id'] ?? '';

    if ($action == 'approve') {
        if ($current_admin_table == 'users') {
            // الحصول على المسؤول التالي من جدول admins
            $sql_next_admin = "SELECT id FROM admins ORDER BY id ASC LIMIT 1";
            $result_next_admin = $conn->query($sql_next_admin);
            if ($result_next_admin && $result_next_admin->num_rows > 0) {
                $next_admin = $result_next_admin->fetch_assoc();
                $next_admin_id = $next_admin['id'];

                // تحديث الطلب ليرسله للمسؤول الآخر
                $sql = "UPDATE final_exam SET current_admin = ?, current_admin_table = 'admins', current_level = current_level + 1 WHERE id = ?";
                $stmt_update = $conn->prepare($sql);
                $stmt_update->bind_param("ii", $next_admin_id, $request_id);
                if ($stmt_update->execute()) {
                    echo json_encode(['showDatePicker' => false]);
                } else {
                    error_log("Error updating to next admin: " . $stmt_update->error);
                    echo json_encode(['error' => 'حدث خطأ أثناء تحديث الطلب.']);
                }
            } else {
                // لا يوجد مسؤول آخر، يتم تغيير حالة الطلب إلى "Approved"
                $sql = "UPDATE final_exam SET status = 'Approved', processed_by = ? WHERE id = ?";
                $stmt_update = $conn->prepare($sql);
                $stmt_update->bind_param("ii", $current_admin_id, $request_id);
                if ($stmt_update->execute()) {
                    echo json_encode(['showDatePicker' => true, 'requestId' => $request_id]);
                } else {
                    error_log("Error approving request: " . $stmt_update->error);
                    echo json_encode(['error' => 'حدث خطأ أثناء تحديث الطلب.']);
                }
            }
        } elseif ($current_admin_table == 'admins') {
            // الحصول على جميع المسؤولين
            $sql_admins = "SELECT id FROM admins ORDER BY id ASC";
            $admins_result = $conn->query($sql_admins);
            if (!$admins_result) {
                die("Error executing query: " . $conn->error);
            }

            $admin_ids = [];
            while ($admin = $admins_result->fetch_assoc()) {
                $admin_ids[] = $admin['id'];
            }

            // البحث عن المسؤول الحالي في القائمة
            $current_index = array_search($current_admin_id, $admin_ids);

            if ($current_index !== false && $current_index < count($admin_ids) - 1) {
                // يوجد مسؤول تالي
                $next_admin_id = $admin_ids[$current_index + 1];

                // تحديث الطلب ليرسله للمسؤول التالي
                $sql = "UPDATE final_exam SET current_admin = ?, current_level = current_level + 1 WHERE id = ?";
                $stmt_update = $conn->prepare($sql);
                $stmt_update->bind_param("ii", $next_admin_id, $request_id);
                if ($stmt_update->execute()) {
                    echo json_encode(['showDatePicker' => false]);
                } else {
                    error_log("Error updating to next admin: " . $stmt_update->error);
                    echo json_encode(['error' => 'حدث خطأ أثناء تحديث الطلب.']);
                }
            } else {
                // لا يوجد مسؤول آخر، يتم تغيير حالة الطلب إلى "Approved"
                $sql = "UPDATE final_exam SET status = 'Approved', processed_by = ? WHERE id = ?";
                $stmt_update = $conn->prepare($sql);
                $stmt_update->bind_param("ii", $current_admin_id, $request_id);
                if ($stmt_update->execute()) {
                    echo json_encode(['showDatePicker' => true, 'requestId' => $request_id]);
                } else {
                    error_log("Error approving request: " . $stmt_update->error);
                    echo json_encode(['error' => 'حدث خطأ أثناء تحديث الطلب.']);
                }
            }
        }
        exit();
    } elseif ($action == 'reject') {
        // تحديث حالة الطلب إلى "Rejected" وإضافة سبب الرفض
        $rejection_reason = $_POST['rejection_reason'] ?? '';
        if (empty($rejection_reason)) {
            echo json_encode(['error' => 'يرجى إدخال سبب الرفض.']);
            exit();
        }

        $sql = "UPDATE final_exam SET status = 'Rejected', rejection_reason = ?, processed_by = ? WHERE id = ?";
        $stmt_reject = $conn->prepare($sql);
        $stmt_reject->bind_param("sii", $rejection_reason, $current_admin_id, $request_id);
        if ($stmt_reject->execute()) {
            echo json_encode(['showDatePicker' => false]);
        } else {
            error_log("Error rejecting request: " . $stmt_reject->error);
            echo json_encode(['error' => 'حدث خطأ أثناء رفض الطلب.']);
        }
        exit();
    } elseif ($action == 'set_exam_date') {
        $exam_date = $_POST['exam_date'] ?? '';

        // التحقق من أن التاريخ موجود وصحيح
        if (empty($exam_date)) {
            echo json_encode(['error' => 'تاريخ الاختبار غير محدد.']);
            exit();
        }

        // التحقق من صحة التاريخ
        if (!validateDate($exam_date)) {
            echo json_encode(['error' => 'تاريخ الاختبار غير صالح.']);
            exit();
        }

        // تحديث الطلب بتاريخ الاختبار
        $sql_update_exam_date = "UPDATE final_exam SET exam_date = ?, status = 'Approved' WHERE id = ?";
        $stmt_update_exam_date = $conn->prepare($sql_update_exam_date);
        $stmt_update_exam_date->bind_param("si", $exam_date, $request_id);

        if ($stmt_update_exam_date->execute()) {
            echo json_encode(['showDatePicker' => false, 'success' => 'تم تعيين تاريخ الاختبار بنجاح.']);
        } else {
            error_log("Error updating exam_date: " . $stmt_update_exam_date->error);
            echo json_encode(['error' => 'حدث خطأ أثناء تعيين تاريخ الاختبار.']);
        }
        exit();
    } elseif ($action == 'Under_Progrees') {
        // إرسال الطلب إلى مجلس الكلية
        $sql = "UPDATE final_exam SET status = 'Under_Progrees', processed_by = ? WHERE id = ?";
        $stmt_send_council = $conn->prepare($sql);
        $stmt_send_council->bind_param("ii", $current_admin_id, $request_id);
        if ($stmt_send_council->execute()) {
            echo json_encode(['success' => true]);
        } else {
            error_log("Error sending to council: " . $stmt_send_council->error);
            echo json_encode(['success' => false, 'error' => 'حدث خطأ أثناء إرسال الطلب إلى مجلس الكلية.']);
        }
        exit();
    }
}

// لإضافة اسم المادة واسم الدكتور
if ($current_admin_table == 'users') {
    $sql_requests = "SELECT final_exam.*, students.name AS student_name, students.department_id AS student_department_id,
        Course.name AS course_name, professors.name AS professor_name
        FROM final_exam
        JOIN students ON final_exam.student_id = students.id
        LEFT JOIN Course ON final_exam.course_id = Course.id
        LEFT JOIN professors ON final_exam.professor_id = professors.id
        WHERE final_exam.status NOT IN ('Approved', 'Rejected')
        AND final_exam.current_admin = ?
        AND final_exam.current_admin_table = ?
        AND students.department_id = ?"; 

    $stmt_requests = $conn->prepare($sql_requests);
    $stmt_requests->bind_param("isi", $current_admin_id, $current_admin_table, $current_admin_department_id);
} elseif ($current_admin_table == 'admins') {
    $sql_requests = "SELECT final_exam.*, students.name AS student_name,
        Course.name AS course_name, professors.name AS professor_name
        FROM final_exam
        JOIN students ON final_exam.student_id = students.id
        LEFT JOIN Course ON final_exam.course_id = Course.id
        LEFT JOIN professors ON final_exam.professor_id = professors.id
        WHERE final_exam.status NOT IN ('Approved', 'Rejected')
        AND final_exam.current_admin = ?
        AND final_exam.current_admin_table = ?";

    $stmt_requests = $conn->prepare($sql_requests);
    $stmt_requests->bind_param("is", $current_admin_id, $current_admin_table);
}

$stmt_requests->execute();
$result = $stmt_requests->get_result();

if (!$result) {
    die("Error executing query: " . $conn->error);
}

// وظيفة للتحقق من صحة التاريخ
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة المسؤول - إدارة الطلبات</title>
   
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <link href="../css/admin.css" rel="stylesheet">
    <script src="../JS/admin.js" defer></script>
</head>
<body>


    <header class="header navbar">
        <?php echo "<div class='admin-name'> مرحبا: " . htmlspecialchars($current_admin_name) . "</div>"; ?>
        <div class="nav">
            <a href="logout.php">تسجيل الخروج</a>
        </div>
    </header>


    <div class="container">
   
    </div>

    <footer>
    
        <p>&copy; 2024 بوابة تسهيل. جميع الحقوق محفوظة.</p>
    </footer>

</body>
</html>
