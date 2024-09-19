<?php
include 'db_connection.php';

if (isset($_GET['request_id']) && isset($_GET['exam_date'])) {
    $request_id = intval($_GET['request_id']);
    $exam_date = $conn->real_escape_string($_GET['exam_date']);

    // تحقق من صحة تاريخ الاختبار (يمكنك إضافة مزيد من التحقق هنا)
    $date_format = 'Y-m-d H:i:s';
    $d = DateTime::createFromFormat($date_format, $exam_date);
    if (!($d && $d->format($date_format) === $exam_date)) {
        die("التاريخ غير صالح. الرجاء استخدام التنسيق YYYY-MM-DD HH:MM:SS.");
    }

    // تحديث موعد الاختبار وحالة الطلب في قاعدة البيانات
    $sql = "UPDATE requests SET status='Approved', exam_date='$exam_date' WHERE id=$request_id";
    
    if ($conn->query($sql) === TRUE) {
        // جلب بيانات الطالب لإرسال رسالة تأكيد
        $sql_request = "SELECT student_name, student_email FROM requests WHERE id=$request_id";
        $request_result = $conn->query($sql_request);
        if ($request_result && $request_result->num_rows > 0) {
            $request_row = $request_result->fetch_assoc(); 
            $student_name = $request_row['student_name'];
            $student_email = $request_row['student_email'];

            // إعداد البريد الإلكتروني
            $to = $student_email;
            $subject = "تمت الموافقة على طلبك وتحديد موعد الاختبار";
            $message = "عزيزي $student_name,\n\nتهانينا! تم الموافقة على طلبك.\nموعد الاختبار الخاص بك هو: $exam_date.\n\nمع تحياتنا,\nفريق الإدارة";
            $headers = "From: admin@example.com"; // تأكد من تعديل البريد الإلكتروني هنا

            mail($to, $subject, $message, $headers);
        }

        // إعادة التوجيه إلى لوحة التحكم مع رسالة نجاح
        header("Location: admin_dashboard.php?message=تم تحديث موعد الاختبار بنجاح");
        exit();
    } else {
        die("خطأ في تحديث الطلب: " . $conn->error);
    }
} else {
    header("Location: admin_dashboard.php");
    exit();
}
?>
