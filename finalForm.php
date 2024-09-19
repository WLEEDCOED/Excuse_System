<?php
session_start();
include 'db_connection.php'; // تأكد من أن مسار ملف الاتصال بقاعدة البيانات صحيح
include 'header2.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // استرجاع بيانات الطلب
    $class = $conn->real_escape_string($_POST['class']);
    $date = $conn->real_escape_string($_POST['date']);
    $excuse = $conn->real_escape_string($_POST['excuse']);
    $description = $conn->real_escape_string($_POST['description']);
    $student_id = $_SESSION['user_id']; // التأكد من أن الطالب مسجل دخوله

    // معالجة رفع الملف
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["file"]["name"]);
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // التحقق من حجم الملف (محدد بـ 5MB)
    if ($_FILES["file"]["size"] > 5000000) {
        echo "عذراً، حجم الملف كبير جداً.";
        $uploadOk = 0;
    }

    // السماح فقط بأنواع معينة من الملفات (PDF, DOC, DOCX)
    if ($fileType != "pdf" && $fileType != "doc" && $fileType != "docx") {
        echo "عذراً، فقط الملفات من نوع PDF, DOC, DOCX مسموح بها.";
        $uploadOk = 0;
    }

    // التحقق مما إذا كانت هناك أي أخطاء في عملية الرفع
    if ($uploadOk == 0) {
        echo "عذراً، لم يتم رفع الملف.";
    } else {
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
            // إدخال بيانات الطلب في قاعدة البيانات
            $sql = "INSERT INTO requests (student_id, class, date, excuse, description, file_path, status, current_admin) 
                    VALUES ('$student_id', '$class', '$date', '$excuse', '$description', '$target_file', 'Pending', '1')";

            if ($conn->query($sql) === TRUE) {
                echo "تم إرسال طلبك بنجاح.";
            } else {
                echo "خطأ: " . $sql . "<br>" . $conn->error;
            }
        } else {
            echo "عذراً، حدث خطأ أثناء رفع الملف.";
        }
    }

    $conn->close();
}

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إرسال الامتحان النهائي</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #c8e6c9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            direction: rtl;
        }
        .admin-info {
    display: flex;
    align-items: center;
    position: relative;
}

.admin-icon {
    cursor: pointer;
    background-color:white ;
    border-radius: 50%;
    padding: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    width: 40px;
    height: 40px;
    margin-left: 10px;
}

.dropdown-menu {
    display: none;
    position: absolute;
    top: 50px;
    right: 0;
    background-color: white;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 5px;
    overflow: hidden;
    z-index: 1000;
}

.dropdown-menu a {
    display: block;
    padding: 10px 20px;
    text-decoration: none;
    color: #333;
    background-color: #fff;
}

.dropdown-menu a:hover {
    background-color: #f1f1f1;
}
        .form-container {
            background-color: #4caf50;
            padding: 20px;
            border-radius: 15px;
            width: 400px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .form-container h2 {
            color: #ffffff;
            margin-bottom: 20px;
        }

        .form-container input, .form-container textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: none;
            border-radius: 5px;
            font-size: 16px;
        }

        .form-container input[type="file"] {
            background-color: #ffffff;
            color: #000000;
            padding: 5px;
        }

        .form-container button {
            background-color: #1b5e20;
            color: #ffffff;
            border: none;
            padding: 15px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 20px;
        }

        .form-container button:hover {
            background-color: #2e7d32;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>إرسال الامتحان النهائي</h2>
        
        <form action="" method="post" enctype="multipart/form-data">
            <input type="text" name="class" placeholder="اسم المادة" required>
            <input type="date" name="date" placeholder="تاريخ الامتحان" required>
            <input type="text" name="excuse" placeholder="العذر" required>
            <textarea name="description" placeholder="الوصف" rows="4" required></textarea>
            <input type="file" name="file" required>
            <button type="submit">إرسال</button>
        </form>
    </div>
</body>
</html>
