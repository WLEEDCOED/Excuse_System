<?php
session_start();
include 'db_connection.php';

// Ensure the user is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    echo "غير مصرح بالوصول";
    exit;
}

$student_id = $_SESSION['user_id'];

// Prepare and execute the query to fetch student requests
$sql = "SELECT * FROM requests WHERE student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch all admins and professors
$admins_sql = "SELECT * FROM users WHERE role = 'admin'";
$professors_sql = "SELECT * FROM users WHERE role = 'professor'";

$admins = [];
$professors = [];

foreach ([$admins_sql => &$admins, $professors_sql => &$professors] as $query => &$array) {
    $result = $conn->query($query);
    if ($result === false) {
        die("Error executing query: " . $conn->error);
    }
    while ($row = $result->fetch_assoc()) {
        $array[$row['id']] = $row['name'];
    }
}

$output = '';
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $output .= "<div class='request'>";
        $output .= "<h3>طلب رقم: " . $row['id'] . "</h3>";
        
        if ($row['status'] == 'Approved') {
            $approved_by_ids = !empty($row['approved_by']) ? explode(',', $row['approved_by']) : [];
            if (!empty($row['exam_date']) && $row['exam_date'] != '0000-00-00') {
                $formatted_date = date('Y-m-d', strtotime($row['exam_date']));
                $output .= "<p>موعد الاختبار هو: " . $formatted_date . "</p>";
            } else {
                $output .= "<p>موعد الاختبار سيتم تحديده قريبًا.</p>";
            }
        } elseif ($row['status'] == 'Rejected') {
            $output .= "<p class='status-rejected'>تم رفض طلبك.</p>";
        } else {
            $output .= "<p>الحالة: " . $row['status'] . "</p>";
        }

        if (!empty($row['approved_by'])) {
            $approved_by_names = [];
            foreach ($approved_by_ids as $admin_id) {
                if (isset($admins[$admin_id])) {
                    $approved_by_names[] = $admins[$admin_id];
                }
            }
            $output .= "<p>تمت الموافقة من قبل المسؤولين: " . implode(', ', $approved_by_names) . "</p>";
        }

        if (!empty($row['professor_approved_by'])) {
            if (isset($professors[$row['professor_approved_by']])) {
                $output .= "<p class='status-approved-professor'>تمت الموافقة من قبل الدكتور: " . $professors[$row['professor_approved_by']] . "</p>";
            }
        }

        if (isset($admins[$row['current_admin']])) {
            $output .= "<p>مكان الطلب: " . $admins[$row['current_admin']] . "</p>";
        } else {
            $output .= "<p>لا يوجد مسؤول حالي للمراجعة.</p>";
        }

        $output .= "</div>";
    }
} else {
    $output = "<p>لا توجد طلبات حتى الآن.</p>";
}

echo $output;
?>
