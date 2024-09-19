<?php
session_start();
include 'db_connection.php'; // تأكد من تضمين ملف الاتصال بقاعدة البيانات

// جلب اسم المسؤول الحالي من قاعدة البيانات
$current_admin_id = $_SESSION['user_id'];
$sql_admin_name = "SELECT name FROM users WHERE id = $current_admin_id";
$result_admin_name = $conn->query($sql_admin_name);

if ($result_admin_name && $result_admin_name->num_rows > 0) {
    $admin_row = $result_admin_name->fetch_assoc();
    $current_admin_name = $admin_row['name'];
} else {
    $current_admin_name = "Unknown Admin"; // قيمة افتراضية إذا لم يتم العثور على الاسم
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $request_id = intval($_POST['request_id']);
    $action = $_POST['action'];

    if ($action == 'approve') {
        // جلب جميع المسؤولين وترتيبهم حسب ID
        $sql_admins = "SELECT id FROM users WHERE role = 'admin' ORDER BY id ASC";
        $admins_result = $conn->query($sql_admins);
        $next_admin_id = null;
        $found = false;

        // إيجاد المسؤول التالي
        while ($admin = $admins_result->fetch_assoc()) {
            if ($found) {
                $next_admin_id = $admin['id'];
                break;
            }
            if ($admin['id'] == $current_admin_id) {
                $found = true;
            }
        }

        // تحديث الطلب ليرسله للمسؤول التالي
        if ($next_admin_id) {
            $sql = "UPDATE requests SET current_admin = $next_admin_id, current_level = current_level + 1 WHERE id = $request_id";
            $conn->query($sql);
            echo json_encode(['showDatePicker' => false]);
        } else {
            // إذا كان لا يوجد مسؤول آخر، يتم تغيير حالة الطلب إلى "Approved"
            $sql = "UPDATE requests SET status = 'Approved', processed_by = $current_admin_id WHERE id = $request_id";
            $conn->query($sql);
            echo json_encode(['showDatePicker' => true, 'requestId' => $request_id]);
        }

        exit();
    } elseif ($action == 'reject') {
        // تحديث حالة الطلب إلى "Rejected" وإضافة سبب الرفض
        $rejection_reason = $conn->real_escape_string($_POST['rejection_reason']); // تأكد من التعامل مع السبب بشكل آمن
        $sql = "UPDATE requests SET status = 'Rejected', rejection_reason = '$rejection_reason', processed_by = $current_admin_id WHERE id = $request_id";
        $conn->query($sql);

        echo json_encode(['showDatePicker' => false]);
        exit();
    } elseif ($action == 'set_exam_date') {
        $exam_date = $_POST['exam_date'];

        // تحديث الطلب بتاريخ الامتحان
        $sql_update_exam_date = "UPDATE requests SET exam_date = ?, status = 'Approved' WHERE id = ?";
        $stmt_update_exam_date = $conn->prepare($sql_update_exam_date);
        $stmt_update_exam_date->bind_param("si", $exam_date, $request_id);
        if (!$stmt_update_exam_date->execute()) {
            error_log("Execution error: " . $stmt_update_exam_date->error);
        }

        echo json_encode(['showDatePicker' => false]);
        exit();
    }
}

// استعلام لجلب الطلبات
$sql_requests = "SELECT requests.*, users.name AS student_name 
                 FROM requests 
                 JOIN users ON requests.student_id = users.id 
                 WHERE requests.status = 'Pending' AND requests.current_admin = $current_admin_id";

$result = $conn->query($sql_requests);

if (!$result) {
    die("Error executing query: " . $conn->error);
}
?>


<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة المسؤول - إدارة الطلبات</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
        }

        .header {
            background-color: #00a86b;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #fff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header .logo {
            font-size: 24px;
            font-weight: bold;
        }

        .header .nav a {
            margin-left: 20px;
            text-decoration: none;
            color: #fff;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        .header .nav a:hover {
            color: #e0f5f0;
        }

        .container {
    max-width: 900px;
    margin: 40px auto;
    padding: 20px;
    background-color: #fff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
    display: flex;
    flex-direction: column; /* لتظهر الطلبات تحت بعضها */
    gap: 20px; /* مسافة بين كل طلب وآخر */
}

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }

        .request {
    background-color: #00a86b;
    border-radius: 10px;
    padding: 20px;
    color: #fff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column; /* التأكد من أن المحتويات داخل الطلب منظمة عمودياً */
    gap: 10px; /* إضافة مسافة بين العناصر داخل الطلب */
}

.request-info {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px; /* إضافة مسافة بين العناصر داخل request-info */
}

        .admin-name {
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 10px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s ease, color 0.3s ease;
            margin: 5px;
        }

        .btn-accept {
            background-color: #fff;
            color: #00a86b;
        }

        .btn-reject {
            background-color: #fff;
            color: #d9534f;
        }

        .btn:hover {
            background-color: #00a86b;
            color: #fff;
        }

        .btn-reject:hover {
            background-color: #d9534f;
            color: #fff;
        }

        .btn-display {
            background-color: #fff;
            color: #00a86b;
        }

        .btn-display:hover {
            background-color: #00a86b;
            color: #fff;
        }

        .details {
            display: none;
            background-color: #fff;
            color: #333;
            padding: 15px;
            border-radius: 10px;
            margin-top: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .show-details {
            display: block;
        }

        .success-message {
            display: none;
            background-color: #28a745;
            color: #fff;
            padding: 10px;
            border-radius: 10px;
            text-align: center;
            margin-top: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .success-show {
            display: block;
        }

        #datePicker {
            display: none;
        }
  

    </style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            function loadRequests() {
                $.ajax({
                    url: 'get_requests.php',
                    type: 'GET',
                    success: function(data) {
                        if (data.trim() === '') {
                            $('.container').html(`<p style='color: black; text-align: center;'>لا توجد طلبات للمراجعة في الوقت الحالي.</p>`);
                        } else {
                            $('.container').html(data);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error loading requests:", error);
                    }
                });
            }

            loadRequests();
            setInterval(loadRequests, 30000);

            window.handleApproval = function(requestId) {
                $.ajax({
                    url: 'admin_dashboard.php',
                    type: 'POST',
                    data: { action: 'approve', request_id: requestId },
                    dataType: 'json',
                    success: function(data) {
                        if (data.showDatePicker) {
                            setExamDate(requestId);
                        } else {
                            removeRequest(requestId);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error approving request:", error);
                    }
                });
            };

            window.handleRejection = function(requestId) {
                Swal.fire({
                    title: 'اكتب سبب الرفض',
                    input: 'textarea',
                    inputPlaceholder: 'ادخل سبب الرفض هنا...',
                    showCancelButton: true,
                    confirmButtonText: 'رفض الطلب',
                    cancelButtonText: 'إلغاء',
                    preConfirm: (rejectionReason) => {
                        if (!rejectionReason) {
                            Swal.showValidationMessage('يرجى إدخال سبب الرفض');
                        }
                        return rejectionReason;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const rejectionReason = result.value;
                        $.ajax({
                            url: 'admin_dashboard.php',
                            type: 'POST',
                            data: {
                                action: 'reject',
                                request_id: requestId,
                                rejection_reason: rejectionReason
                            },
                            dataType: 'json',
                            success: function(data) {
                                removeRequest(requestId);
                                showSuccess();
                            },
                            error: function(xhr, status, error) {
                                console.error("Error rejecting request:", error);
                            }
                        });
                    }
                });
            };

            function removeRequest(requestId) {
                $('#request-' + requestId).fadeOut(400, function() {
                    $(this).remove();
                    if ($('.request').length === 0) {
                        $('.container').html("<p style='color: black; text-align: center;'>لا توجد طلبات للمراجعة في الوقت الحالي.</p>");
                    }
                });
            }

            window.setExamDate = function(requestId) {
                Swal.fire({
                    title: 'حدد تاريخ الامتحان',
                    html: '<input type="date" id="examDatePicker" class="swal2-input">',
                    showCancelButton: true,
                    confirmButtonText: 'تأكيد',
                    cancelButtonText: 'إلغاء',
                    preConfirm: () => {
                        const examDate = $('#examDatePicker').val();
                        if (!examDate) {
                            Swal.showValidationMessage('يرجى اختيار تاريخ الامتحان');
                        }
                        return examDate;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const examDate = result.value;
                        $.ajax({
                            url: 'admin_dashboard.php',
                            type: 'POST',
                            data: {
                                action: 'set_exam_date',
                                request_id: requestId,
                                exam_date: examDate
                            },
                            dataType: 'json',
                            success: function(response) {
                                removeRequest(requestId);
                                showSuccess();
                            },
                            error: function(xhr, status, error) {
                                console.error("Error setting exam date:", error);
                            }
                        });
                    }
                });
            };
            window.toggleDetails = function(id) {
            $('#details-' + id).toggleClass('show-details');
        };

     
            window.showSuccess = function() {
                Swal.fire({
                    icon: 'success',
                    title: 'تم تحديث الطلب بنجاح',
                    showConfirmButton: false,
                    timer: 1500
                });
            };
        });
    </script>
<body>
    <header class="header">
        <?php echo "<div class='admin-name'> مرحبا: " . htmlspecialchars($current_admin_name) . "</div>"; ?>
        <div class="nav">
            <a href="logout.php">تسجيل الخروج</a>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        </div>
    </header>

    <div class="container">
        <h2>إدارة الطلبات</h2>

        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div id='request-" . $row['id'] . "' class='request'>";
                echo "<div class='request-info'>";
                echo "<span><strong>اسم الطالب:</strong> " . htmlspecialchars($row['student_id']) . "</span>";
                echo "<span><strong>رقم الطلب:</strong> " . htmlspecialchars($row['id']) . "</span>";
                echo "<span><strong>تاريخ الطلب:</strong> " . htmlspecialchars($row['date']) . "</span>";
                echo "<button class='btn btn-display' onclick='toggleDetails(" . $row['id'] . ")'>عرض العذر</button>";
                echo "</div>";
               
                echo "<div id='details-" . $row['id'] . "' class='request-details'>";
                echo "<p><strong>الوصف:</strong> " . htmlspecialchars($row['description']) . "</p>";
                echo "<p><strong>المرفقات:</strong> <a href='" . htmlspecialchars($row['file_path']) . "' target='_blank'>تحميل المرفق</a></p>";
                
                //zإظهار سبب الرفض إذا كان الطلب مرفوضا
                if ($row['status'] == 'Rejected') {
                    echo "<p><strong>سبب الرفض:</strong> " . htmlspecialchars($row['rejection_reason']) . "</p>";
                }

                echo "<div class='actions'>";
                echo "<button class='btn approve' onclick='handleApproval(" . $row['id'] . ")'>الموافقة</button>";
                echo "<button class='btn reject' onclick='handleRejection(" . $row['id'] . ")'>الرفض</button>";
                echo "</div>"; // نهاية actions

                echo "</div>"; 
                echo "</div>"; // نهاية الطلب
            }
        } else {
            echo "<p style='color: black; text-align: center;'>لا توجد طلبات للمراجعة في الوقت الحالي.</p>";
        }
       
  


        include 'Footer.php';
        ?>
 

    <!-- Date Picker Section -->
    <div id="datePicker">
        <h3>تحديد تاريخ الامتحان</h3>
        <input type="hidden" id="requestIdInput">
        <input type="date" id="examDate">
        <button onclick="setExamDate()">تحديد التاريخ</button>
    </div>

  

</body>
</html>
</body>
</html>
