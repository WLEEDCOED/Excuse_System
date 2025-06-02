<?php
session_start();
include 'db_connection.php';

// التحقق من أن النموذج تم إرساله
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = trim($_POST['id']);  
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $department_id = $_POST['department']; 
    $role_id = 3;

    // التحقق من صحة بيانات القسم
    $valid_departments = [1, 2, 3];
    if (!in_array($department_id, $valid_departments)) {
        $error_message = "القسم المحدد غير صالح.";
    } else {
        // تشفير كلمة المرور باستخدام password_hash
        $hashed_password = mb5($password, PASSWORD_DEFAULT);

       
        $conn->begin_transaction();

        try {
          
            $stmt = $conn->prepare("INSERT INTO students (id, name, email, password, role_id, department_id) VALUES (?, ?, ?, ?, ?, ?)");

          
            if (!$stmt) {
                throw new Exception("خطأ في الاستعلام: " . $conn->error);
            }

            // ربط المتغيرات مع الاستعلام
            $stmt->bind_param("isssii", $id, $name, $email, $hashed_password, $role_id, $department_id);

            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }

            // تأكيد التغييرات إذا تم التنفيذ بنجاح
            $conn->commit();
            $success_message = "تم إنشاء الحساب بنجاح. يمكنك الآن تسجيل الدخول.";
            
         

        } catch (Exception $e) {
            // إلغاء المعاملة في حال حدوث خطأ
            $conn->rollback();
            $error_message = "حدث خطأ: " . $e->getMessage();
        }

        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل حساب طالب جديد</title>

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="CSS/register.css">
    <style>
       
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f4f7f6;
            background-image: url('Image/BG-login.png');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            direction: rtl;
            color: #333;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

    
        .header, .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background-color: rgba(42, 157, 143, 0.9);
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
            color: white;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .nav-logo, .header .logo {
            font-size: 24px;
            font-weight: bold;
            color: white;
        }

        .logout-btn, .header .nav a {
            background-color: #e76f51;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.3s ease;
            text-decoration: none;
            font-size: 16px;
        }

        .logout-btn:hover, .header .nav a:hover {
            background-color: #d1495b;
            transform: translateY(-2px);
        }

        
        .container {
            max-width: 500px;
            margin: 100px auto 40px; 
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 24px;
        }

        /* تنسيق الفورم */
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        input[type="text"],
        input[type="password"],
        input[type="email"],
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        input[type="password"]:focus,
        input[type="email"]:focus,
        select:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 8px rgba(52, 152, 219, 0.5);
        }

        /* تنسيق زر الإرسال */
        input[type="submit"] {
            background-color: #2a9d8f;
            color: #fff;
            padding: 12px;
            font-size: 18px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.3s;
            width: 100%;
            box-shadow: 0 4px 8px rgba(39, 174, 96, 0.4);
        }

        input[type="submit"]:hover {
            background-color: #2a9d8f;
            transform: translateY(-2px);
        }

        input::placeholder,
        select option[value=""] {
            color: #aaa;
            font-size: 14px;
        }

        .links {
            margin-top: 20px;
            text-align: center;
        }

        .links a {
            display: block;
            margin-top: 10px;
            color: #3498db;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s;
        }

        .links a:hover {
            color: #2980b9;
        }

        .message {
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            font-size: 16px;
        }

        .success-message {
            background-color: #28a745;
            color: #fff;
        }

        .error-message {
            background-color: #dc3545;
            color: #fff;
        }

        footer {
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 15px 30px;
            text-align: center;
            width: 100%;
            box-shadow: 0px -4px 12px rgba(0, 0, 0, 0.1);
            margin-top: auto;
        }

        footer a {
            color: #e9c46a;
            text-decoration: none;
            margin: 0 10px;
            transition: color 0.3s ease;
            font-weight: bold;
        }

        footer a:hover {
            color: #ffd166;
        }

        footer p {
            margin-top: 10px;
            font-size: 14px;
        }

        /* تصميم متجاوب */
        @media (max-width: 600px) {
            .container {
                padding: 15px;
                margin: 100px auto 20px;
            }

            h2 {
                font-size: 20px;
            }

            input[type="text"],
            input[type="password"],
            input[type="email"],
            select {
                font-size: 14px;
            }

            input[type="submit"] {
                font-size: 16px;
            }

            .links a {
                font-size: 12px;
            }
        }
    </style>
    <!-- إضافة JavaScript للتحقق من صحة البريد الإلكتروني -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.querySelector("form");
            form.addEventListener("submit", function(event) {
                const email = form.email.value;
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(email)) {
                    alert("يرجى إدخال بريد إلكتروني صالح.");
                    event.preventDefault();
                }

                // التحقق من اختيار قسم
                const department = form.department.value;
                if (department === "") {
                    alert("يرجى اختيار قسم.");
                    event.preventDefault();
                }
            });
        });
    </script>
</head>
<body>

    <header class="header navbar">
        <?php echo "<div class='nav-logo'>بوابة تسهيل</div>"; ?>
        <div class="nav">
            <a href="login.php">تسجيل الدخول</a>
        </div>
    </header>

 
    <div class="container">
        <h2>تسجيل حساب طالب جديد</h2>
      
        <?php 
        if (isset($success_message)) {
            echo "<div class='message success-message'>$success_message</div>";
        }
        if (isset($error_message)) {
            echo "<div class='message error-message'>$error_message</div>";
        }
        ?>
        <form action="register.php" method="post">
            <input type="text" name="name" placeholder="الاسم الكامل" required>
            <input type="text" name="id" placeholder="رقم الجامعي" required>
            <input type="email" name="email" placeholder="البريد الإلكتروني" required>
            <input type="password" name="password" placeholder="كلمة المرور" required>
        
            <select name="department" required>
                <option value="">اختر القسم</option>
                <option value="1">علوم الحاسب</option>
                <option value="2">تقنية المعلومات</option>
                <option value="3">نظم المعلومات</option>
            </select>

            <input type="submit" value="تسجيل">

        
            <div class="links">
                <a href="forgot_password.php">نسيت كلمة المرور؟</a>
                <a href="login.php">لديك حساب بالفعل؟ تسجيل الدخول</a>
            </div>
        </form>
    </div>

 
    <footer>
    
        <p>&copy; 2024 بوابة تسهيل. جميع الحقوق محفوظة.</p>
    </footer>

</body>
</html>
