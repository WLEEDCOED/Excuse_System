<?php
session_start();
include 'db_connection.php';

// تأكد من أن المستخدم مسجل دخوله كطالب
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header('Location: login.php');
    exit;
}

// استعلام عن الطلبات المقدمة من الطالب
$sql = "SELECT * FROM requests WHERE student_id='" . $_SESSION['user_id'] . "'";
$result = $conn->query($sql);

// التحقق من نجاح استعلام الطلبات
if ($result === false) {
    die("Error executing query: " . $conn->error);
}

// استعلام عن جميع المسؤولين
$admins_sql = "SELECT * FROM users WHERE role = 'admin'";
$admins_result = $conn->query($admins_sql);

// التحقق من نجاح استعلام المسؤولين
if ($admins_result === false) {
    die("Error executing admin query: " . $conn->error);
}

$admins = [];
while ($admin = $admins_result->fetch_assoc()) {
    $admins[$admin['id']] = $admin['name'];
}

// استعلام عن جميع الدكاترة
$professors_sql = "SELECT * FROM users WHERE role = 'professor'";
$professors_result = $conn->query($professors_sql);

if ($professors_result === false) {
    die("Error executing professor query: " . $conn->error);
}

$professors = [];
while ($professor = $professors_result->fetch_assoc()) {
    $professors[$professor['id']] = $professor['name'];
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة الطالب - طلباتي</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e0f7fa;
            color: #333;
            margin: 0;
            padding: 0;
            direction: rtl;
            text-align: right;
        }

        h2 {
            text-align: center;
            margin: 20px 0;
            color: #00796b;
        }

        .dashboard-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .request {
            border: 1px solid #00796b;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            background-color: #e0f2f1;
        }

        .request h3 {
            margin: 0 0 10px 0;
            color: #004d40;
        }

        .request p {
            margin: 5px 0;
            color: #004d40;
        }

        .request p.status-rejected {
            color: red;
        }

        .request p.status-approved-professor {
            color: green;
        }
    </style>
</head>
<body>

    <div class="dashboard-container">
        <h2>طلباتي</h2>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='request'>";
                echo "<h3>طلب رقم: " . $row['id'] . "</h3>";

                // عرض حالة الطلب وتاريخ الاختبار إذا تم تحديده
                if ($row['status'] == 'Approved') {
                    $approved_by_ids = !empty($row['approved_by']) ? explode(',', $row['approved_by']) : [];
                    // التحقق من عدد المسؤولين الذين وافقوا
                    if (!empty($row['exam_date']) && $row['exam_date'] != '0000-00-00') {
                        $formatted_date = date('Y-m-d', strtotime($row['exam_date']));
                        echo "<p>موعد الاختبار هو: " . $formatted_date . "</p>";
                    } else {
                        echo "<p>موعد الاختبار سيتم تحديده قريبًا.</p>";
                    }
                } elseif ($row['status'] == 'Rejected') {
                    echo "<p class='status-rejected'>تم رفض طلبك.</p>";

                    // عرض سبب الرفض
                    if (!empty($row['rejection_reason'])) {
                        echo "<p>سبب الرفض: " . htmlspecialchars($row['rejection_reason']) . "</p>";
                    }
                } else {
                    echo "<p>الحالة: " . $row['status'] . "</p>";
                }

                // عرض المسؤولين الذين وافقوا على الطلب
                if (!empty($row['approved_by'])) {
                    $approved_by_names = [];
                    foreach ($approved_by_ids as $admin_id) {
                        if (isset($admins[$admin_id])) {
                            $approved_by_names[] = $admins[$admin_id];
                        }
                    }
                    echo "<p>تمت الموافقة من قبل المسؤولين: " . implode(', ', $approved_by_names) . "</p>";
                }

                // عرض الدكتور الذي وافق على الطلب
                if (!empty($row['professor_approved_by'])) {
                    if (isset($professors[$row['professor_approved_by']])) {
                        echo "<p class='status-approved-professor'>تمت الموافقة من قبل الدكتور: " . $professors[$row['professor_approved_by']] . "</p>";
                    }
                }

                // عرض المسؤول الحالي
                if (isset($admins[$row['current_admin']])) {
                    echo "<p>مكان الطلب: " . $admins[$row['current_admin']] . "</p>";
                } else {
                    echo "<p>لا يوجد مسؤول حالي للمراجعة.</p>";
                }

                echo "</div>";
            }
        } else {
            echo "<p>لا توجد طلبات حتى الآن.</p>";
        }
        ?>
    </div>
</body>
</html>
