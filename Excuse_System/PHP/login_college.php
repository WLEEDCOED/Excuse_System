<?php
session_start();
include 'includes/db_connection.php';  

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['email']) && !empty($_POST['password'])) {
     
        $email = $_POST['email'];
        $password = $_POST['password'];
        
        
        $tables = [
            'admins' => 'admin_dashboard.php',
            'users' => 'admin_dashboard.php',
            'professors' => 'prof.php'    
        ];
        
        $user_found = false;
        $error = '';

        foreach ($tables as $table => $redirect_page) {
           
            $stmt = $conn->prepare("SELECT * FROM $table WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
               
                if (md5($password) == $user['password']) {
                    // تعيين المتغيرات في الجلسة
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['name'] = $user['name'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['table'] = $table;  // لمعرفة الجدول الذي ينتمي إليه المستخدم
                    
                    // التحقق من role_id إذا كان موجودًا
                    if (isset($user['role_id'])) {
                        $_SESSION['role_id'] = $user['role_id'];
                    } else {
                        $_SESSION['role_id'] = null;
                    }
                    
                    // إعادة التوجيه إلى الصفحة المناسبة
                    header("Location: $redirect_page");
                    exit;
                } else {
                    $error = "كلمة المرور غير صحيحة.";
                }
            }
            
            $stmt->close();
        }

        if (!$user_found) {
            
            $error = "بيانات تسجيل الدخول غير صحيحة.";
        }
    } else {
        $error = "يرجى ملء جميع الحقول المطلوبة.";
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - بوابة الطلاب</title>

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <link href="../css/Login_collegae.css" rel="stylesheet">

</head>
<body>

  
    <nav class="navbar">
        <div class="nav-logo">بوابة الكليه</div>
    </nav>

   
    <div class="login-wrapper">
        <div class="login-container">
           
            <a href="Index.php">
                <img src="Image/letter-x.png" alt="إغلاق" class="close-icon"> <!-- تأكد من أن مسار الصورة صحيح -->
            </a>

            
          
            <h2>تسجيل الدخول</h2>

        
            <?php if (isset($error) && $error != '') { echo "<p class='error'>$error</p>"; } ?>

         
            <form method="POST" action="">
                <input type="email" name="email" placeholder="البريد الإلكتروني" required>
                <input type="password" name="password" placeholder="كلمة المرور" required>
                <button type="submit">تسجيل الدخول</button>
            </form>

         
            <a href="forgot_password.php">نسيت كلمة المرور؟</a>
        </div>
    </div>

    <footer>
     
        <p>&copy; 2024 بوابة تسهيل. جميع الحقوق محفوظة.</p>
    </footer>

</body>
</html>