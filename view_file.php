<?php
session_start();
include 'db_connection.php';

// تأكد من أن المستخدم مسجل دخوله كمسؤول
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die('غير مصرح لك بالوصول إلى هذه الصفحة.');
}

if (!isset($_GET['file']) || empty($_GET['file'])) {
    die('لم يتم تحديد ملف.');
}

$file_path = $_GET['file'];

// التحقق من وجود الملف وأنه ضمن المجلد المسموح به
$allowed_dir = 'uploads/'; // تأكد من تغيير هذا إلى المجلد الصحيح حيث يتم تخزين الملفات المرفقة
$real_path = realpath($allowed_dir . basename($file_path));

if ($real_path === false || strpos($real_path, realpath($allowed_dir)) !== 0) {
    die('الملف غير موجود أو غير مصرح بالوصول إليه.');
}

// الحصول على نوع MIME للملف
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $real_path);
finfo_close($finfo);

// تحديد كيفية عرض الملف بناءً على نوعه
switch ($mime_type) {
    case 'application/pdf':
        header('Content-Type: application/pdf');
        readfile($real_path);
        break;
    case 'image/jpeg':
    case 'image/png':
    case 'image/gif':
        header('Content-Type: ' . $mime_type);
        readfile($real_path);
        break;
    case 'text/plain':
        header('Content-Type: text/plain');
        readfile($real_path);
        break;
    default:
        // للملفات الأخرى، قم بتنزيلها
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
        header('Content-Length: ' . filesize($real_path));
        readfile($real_path);
        break;
}