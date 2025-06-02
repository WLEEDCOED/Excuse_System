<?php
session_start();
include 'db_connection.php';

// التأكد من تسجيل الدخول كطالب
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != '3') {
    header('Location: login.php');
    exit;
}

// جلب معرف الطالب من الجلسة
$student_id = $_SESSION['user_id'];

// التأكد من أن رقم الطلب تم إرساله
if (isset($_POST['request_id']) && !empty($_POST['request_id'])) {
    $request_id = intval($_POST['request_id']);

    // استعلام جلب الطلب بناءً على معرف الطالب ورقم الطلب
    $sql = "SELECT * FROM requests WHERE student_id = ? AND id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $student_id, $request_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo "<div class='request' id='request-" . $row['id'] . "'>";
        echo "<h3>طلب رقم: " . $row['id'] . "</h3>";

        // عرض حالة الطلب وتاريخ الاختبار إذا تم تحديده
        if ($row['status'] == 'approved') {
            if (!empty($row['exam_date']) && $row['exam_date'] != '0000-00-00') {
                $formatted_date = date('Y-m-d', strtotime($row['exam_date']));
                echo "<p>تم قبول طلبك. موعد الاختبار هو: " . $formatted_date . "</p>";
            } else {
                echo "<p>تم قبول طلبك. موعد الاختبار سيتم تحديده قريبًا.</p>";
            }
        } elseif ($row['status'] == 'rejected') {
            echo "<p class='status-rejected'>تم رفض طلبك.</p>";

            if (!empty($row['rejection_reason'])) {
                echo "<p>سبب الرفض: " . htmlspecialchars($row['rejection_reason']) . "</p>";
            }
        } elseif ($row['status'] === 'Sent to Council') {
            echo "<p>الحالة: قيد المراجعة من مجلس الكلية</p>";
        } else {
            echo "<p>الحالة: " . htmlspecialchars($row['status']) . "</p>";
        }

        echo "</div>";
    } else {
        echo "<p>لم يتم العثور على الطلب.</p>";
    }
} else {
    echo "<p>يرجى إدخال رقم طلب صالح.</p>";
}
?>
