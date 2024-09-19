<?php
include 'db_connection.php'; 
session_start();

$current_admin_id = $_SESSION['user_id'];

// جلب الطلبات التي تكون حالتها 'Pending' والمخصصة للمسؤول الحالي
// مع ربط جدول الطلبات بجدول المستخدمين للحصول على اسم الطالب
$sql_requests = "SELECT r.*, u.name as student_name 
                 FROM requests r 
                 JOIN users u ON r.student_id = u.id 
                 WHERE r.status = 'Pending' AND r.current_admin = $current_admin_id";
$result = $conn->query($sql_requests);

// عرض العنوان الرئيسي
echo "<h2>إدارة الطلبات</h2>";

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<div id='request-" . $row['id'] . "' class='request'>";
        echo "<div class='request-info'>";
        echo "<span><strong>اسم الطالب:</strong> " . htmlspecialchars($row['student_name']) . "</span>";
        echo "<span><strong> رقم الجامعي:</strong> " . htmlspecialchars($row['student_id']) . "</span>";
        echo "<span><strong>رقم الطلب:</strong> " . htmlspecialchars($row['id']) . "</span>";
        echo "<span><strong>تاريخ الطلب:</strong> " . htmlspecialchars($row['date']) . "</span>";
        echo "<button class='btn btn-display' onclick='toggleDetails(" . $row['id'] . ")'>عرض العذر</button>";
        echo "</div>";

        echo "<div id='details-" . $row['id'] . "' class='details'>";
        echo "<p><strong>المادة:</strong> " . htmlspecialchars($row['class']) . "</p>";
        echo "<p><strong>الوصف:</strong> " . htmlspecialchars($row['description']) . "</p>";
        echo "<p><strong>العذر:</strong> " . htmlspecialchars($row['excuse']) . "</p>";
        echo "<p><strong>تاريخ الطلب:</strong> " . htmlspecialchars($row['date']) . "</p>";

        if ($row['file_path']) {
            echo "<p><strong>ملف مرفق:</strong> <a href='view_file.php?file=" . urlencode($row['file_path']) . "' target='_blank'>عرض الملف</a></p>";
        }

        echo "<div class='actions'>";
        echo "<form method='POST' action='admin_dashboard.php'>";
        echo "<input type='hidden' name='request_id' value='" . $row['id'] . "'>";
        echo "<button type='button' class='btn btn-accept' onclick='handleApproval(" . $row['id'] . ")'>قبول</button>";
        echo "<button type='button' class='btn btn-reject' onclick='handleRejection(" . $row['id'] . ")'>رفض</button>";
        echo "</form>";
        echo "</div>"; // End of actions div

        echo "</div>"; // End of details div

        echo "<div id='success-" . $row['id'] . "' class='success-message'>";
        echo "تمت الموافقة";
        echo "</div>"; // End of success div

        echo "</div>"; // End of request div
    }
} else {
    // عرض رسالة إذا لم تكن هناك طلبات
    echo "<p style='color: black; text-align: center;'>لا توجد طلبات للمراجعة في الوقت الحالي.</p>";
}

?>