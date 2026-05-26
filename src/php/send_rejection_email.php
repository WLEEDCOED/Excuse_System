<?php
function sendRejectionEmail($student_name, $student_email) {
    $subject = "طلبك تم رفضه";
    $message = "عزيزي $student_name,\n\nعذرًا، طلبك قد تم رفضه.\n\nمع تحياتنا,\nفريق الإدارة";
    $headers = "From: yyyoih@gmail.com"; // تأكد من تعديل البريد الإلكتروني هنا

    // إرسال البريد الإلكتروني
    if (mail($student_email, $subject, $message, $headers)) {
        return true;
    } else {
        return false;
    }
}
?>
