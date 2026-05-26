<?php
session_start();
include '../includes/db_connection.php';  // ØªØ£ÙƒØ¯ Ù…Ù† Ø£Ù† Ù…Ø³Ø§Ø± Ù…Ù„Ù Ø§Ù„Ø§ØªØµØ§Ù„ Ø¨Ù‚Ø§Ø¹Ø¯Ø© Ø§Ù„Ø¨ÙŠØ§Ù†Ø§Øª ØµØ­ÙŠØ­

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['email']) && !empty($_POST['password'])) {
        // Ø§Ø³ØªØ®Ø¯Ø§Ù… Ø§Ù„Ø§Ø³ØªØ¹Ù„Ø§Ù…Ø§Øª Ø§Ù„Ù…Ø­Ù…ÙŠØ© Ù„Ù…Ù†Ø¹ Ø­Ù‚Ù† SQL
        $email = $_POST['email'];
        $password = $_POST['password'];
        
        // Ù‚Ø§Ø¦Ù…Ø© Ø§Ù„Ø¬Ø¯Ø§ÙˆÙ„ Ø§Ù„ØªÙŠ Ø³Ù†Ø¨Ø­Ø« ÙÙŠÙ‡Ø§ Ø¹Ù† Ø§Ù„Ù…Ø³ØªØ®Ø¯Ù…
        $tables = [
            'students' => 'Home.php',
              
        ];
        
        $user_found = false;
        $error = '';

        foreach ($tables as $table => $redirect_page) {
            // ØªØ­Ø¶ÙŠØ± Ø§Ù„Ø§Ø³ØªØ¹Ù„Ø§Ù…
            $stmt = $conn->prepare("SELECT * FROM $table WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                // Ø§Ù„ØªØ­Ù‚Ù‚ Ù…Ù† ÙƒÙ„Ù…Ø© Ø§Ù„Ù…Ø±ÙˆØ± Ø¨Ø§Ø³ØªØ®Ø¯Ø§Ù… md5 Ø£Ùˆ ÙŠÙ…ÙƒÙ†Ùƒ Ø§Ø³ØªØ®Ø¯Ø§Ù… password_verify ÙÙŠ Ø­Ø§Ù„Ø© Ø§Ù„ØªØ´ÙÙŠØ± Ø¨Ù€ password_hash
                if (md5($password) == $user['password']) {
                    // ØªØ¹ÙŠÙŠÙ† Ø§Ù„Ù…ØªØºÙŠØ±Ø§Øª ÙÙŠ Ø§Ù„Ø¬Ù„Ø³Ø©
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['name'] = $user['name'];
                    $_SESSION['email'] = $user['email'];
                    // Ù„Ù…Ø¹Ø±ÙØ© Ø§Ù„Ø¬Ø¯ÙˆÙ„ Ø§Ù„Ø°ÙŠ ÙŠÙ†ØªÙ…ÙŠ Ø¥Ù„ÙŠÙ‡ Ø§Ù„Ù…Ø³ØªØ®Ø¯Ù…
                    
                    // Ø§Ù„ØªØ­Ù‚Ù‚ Ù…Ù† role_id Ø¥Ø°Ø§ ÙƒØ§Ù† Ù…ÙˆØ¬ÙˆØ¯Ù‹Ø§
                    if (isset($user['role_id'])) {
                        $_SESSION['role_id'] = $user['role_id'];
                    } else {
                        $_SESSION['role_id'] = null;
                    }
                    
                    // Ø¥Ø¹Ø§Ø¯Ø© Ø§Ù„ØªÙˆØ¬ÙŠÙ‡ Ø¥Ù„Ù‰ Ø§Ù„ØµÙØ­Ø© Ø§Ù„Ù…Ù†Ø§Ø³Ø¨Ø©
                    header("Location: $redirect_page");
                    exit;
                } else {
                    $error = "ÙƒÙ„Ù…Ø© Ø§Ù„Ù…Ø±ÙˆØ± ØºÙŠØ± ØµØ­ÙŠØ­Ø©.";
                }
            }
            
            $stmt->close();
        }

        if (!$user_found) {
            // Ø¥Ø°Ø§ Ù„Ù… ÙŠØªÙ… Ø§Ù„Ø¹Ø«ÙˆØ± Ø¹Ù„Ù‰ Ø§Ù„Ù…Ø³ØªØ®Ø¯Ù… Ø£Ùˆ ÙƒØ§Ù†Øª ÙƒÙ„Ù…Ø© Ø§Ù„Ù…Ø±ÙˆØ± ØºÙŠØ± ØµØ­ÙŠØ­Ø©
            $error = "Ø¨ÙŠØ§Ù†Ø§Øª ØªØ³Ø¬ÙŠÙ„ Ø§Ù„Ø¯Ø®ÙˆÙ„ ØºÙŠØ± ØµØ­ÙŠØ­Ø©.";
        }
    } else {
        $error = "ÙŠØ±Ø¬Ù‰ Ù…Ù„Ø¡ Ø¬Ù…ÙŠØ¹ Ø§Ù„Ø­Ù‚ÙˆÙ„ Ø§Ù„Ù…Ø·Ù„ÙˆØ¨Ø©.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ØªØ³Ø¬ÙŠÙ„ Ø§Ù„Ø¯Ø®ÙˆÙ„ - Ø¨ÙˆØ§Ø¨Ø© Ø§Ù„Ø·Ù„Ø§Ø¨</title>
    <!-- Ø¥Ø¶Ø§ÙØ© Ø®Ø· Ù…Ù† Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* Ø¥Ø¹Ø§Ø¯Ø© ØªØ¹ÙŠÙŠÙ† Ø¨Ø¹Ø¶ Ø§Ù„Ø£Ù†Ù…Ø§Ø· Ø§Ù„Ø§ÙØªØ±Ø§Ø¶ÙŠØ© */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f4f4f9;
            background-image: url('../assets/images/BG-login.png');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            direction: rtl;
            color: #333;
        }

        /* ØªÙ†Ø³ÙŠÙ‚ Ø§Ù„Ø±Ø£Ø³ */
        .navbar {
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

        .nav-logo {
            font-size: 24px;
            font-weight: bold;
            color: white;
        }

        .logout-btn {
            background-color: #e76f51;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .logout-btn:hover {
            background-color: #d1495b;
        }

        /* ØªÙ†Ø³ÙŠÙ‚ ØµÙØ­Ø© ØªØ³Ø¬ÙŠÙ„ Ø§Ù„Ø¯Ø®ÙˆÙ„ */
        .login-wrapper {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding-top: 80px; /* Ù„Ù…Ù†Ø¹ Ø§Ù„Ù…Ø­ØªÙˆÙ‰ Ù…Ù† Ø§Ù„Ø§Ø®ØªØ¨Ø§Ø¡ Ø®Ù„Ù Ø§Ù„Ù‡ÙŠØ¯Ø± Ø§Ù„Ø«Ø§Ø¨Øª */
            padding-bottom: 20px;
        }

        .login-container {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            width: 350px;
            text-align: center;
            position: relative;
        }

        /* Ø£ÙŠÙ‚ÙˆÙ†Ø© Ø§Ù„Ø¥ØºÙ„Ø§Ù‚ */
        .close-icon {
            position: absolute;
            top: 15px;
            right: 15px;
            width: 25px;
            height: 25px;
            cursor: pointer;
        }

        /* Ø£ÙŠÙ‚ÙˆÙ†Ø© Ø§Ù„Ø·Ø§Ù„Ø¨ */
        .login-container .student-icon {
            width: 80px;
            margin-bottom: 10px;
        }

        /* Ø§Ù„Ø¹Ù†ÙˆØ§Ù† */
        .login-container h2 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }

        /* Ø­Ù‚ÙˆÙ„ Ø§Ù„Ø¥Ø¯Ø®Ø§Ù„ */
        .login-container input[type="email"],
        .login-container input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            background-color: #fafafa;
            transition: border-color 0.3s ease, background-color 0.3s ease;
        }

        .login-container input[type="email"]:hover,
        .login-container input[type="password"]:hover,
        .login-container input[type="email"]:focus,
        .login-container input[type="password"]:focus {
            border-color: #3498db;
            background-color: #f1f8ff;
            outline: none;
        }

        /* Ø²Ø± Ø§Ù„Ø¥Ø±Ø³Ø§Ù„ */
        .login-container button {
            width: 100%;
            padding: 12px;
            background-color: #2a9d8f;
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .login-container button:hover {
            background-color: #2a9d8f;
            transform: translateY(-2px);
        }

        /* Ø±Ø§Ø¨Ø· Ù†Ø³ÙŠØª ÙƒÙ„Ù…Ø© Ø§Ù„Ù…Ø±ÙˆØ± */
        .login-container a {
            display: block;
            margin-top: 15px;
            color: #3498db;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .login-container a:hover {
            color: #2980b9;
        }

        /* Ø±Ø³Ø§Ù„Ø© Ø§Ù„Ø®Ø·Ø£ */
        .error {
            color: red;
            font-size: 14px;
            margin-bottom: 20px;
        }

        /* ØªÙ†Ø³ÙŠÙ‚ Ø§Ù„ÙÙˆØªØ± */
        footer {
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 15px 30px;
            text-align: center;
            width: 100%;
            box-shadow: 0px -4px 12px rgba(0, 0, 0, 0.1);
        }

        footer a {
            color: #e9c46a;
            text-decoration: none;
            margin: 0 10px;
            transition: color 0.3s ease;
        }

        footer a:hover {
            color: #ffd166;
        }

        footer p {
            margin-top: 10px;
            font-size: 14px;
        }

        /* ØªØµÙ…ÙŠÙ… Ù…ØªØ¬Ø§ÙˆØ¨ */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                align-items: flex-start;
                padding: 10px 20px;
            }

            .logout-btn {
                margin-top: 10px;
                width: 100%;
                text-align: center;
            }

            .login-container {
                width: 90%;
                padding: 30px;
            }

            .close-icon {
                top: 10px;
                right: 10px;
                width: 20px;
                height: 20px;
            }

            footer {
                padding: 15px 20px;
            }

            footer a {
                margin: 0 5px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>

    <!-- Ø§Ù„Ø±Ø£Ø³ (Header) -->
    <nav class="navbar">
        <div class="nav-logo">Ø¨ÙˆØ§Ø¨Ø© Ø§Ù„Ø·Ù„Ø§Ø¨</div>
    </nav>

    <!-- ØµÙØ­Ø© ØªØ³Ø¬ÙŠÙ„ Ø§Ù„Ø¯Ø®ÙˆÙ„ -->
    <div class="login-wrapper">
        <div class="login-container">
            <!-- Ø£ÙŠÙ‚ÙˆÙ†Ø© Ø§Ù„Ø¥ØºÙ„Ø§Ù‚ -->
            <a href="index.php">
                <img src="../assets/images/letter-x.png" alt="Ø¥ØºÙ„Ø§Ù‚" class="close-icon"> <!-- ØªØ£ÙƒØ¯ Ù…Ù† Ø£Ù† Ù…Ø³Ø§Ø± Ø§Ù„ØµÙˆØ±Ø© ØµØ­ÙŠØ­ -->
            </a>

            <!-- Ø£ÙŠÙ‚ÙˆÙ†Ø© Ø§Ù„Ø·Ø§Ù„Ø¨ -->
            <img src="../assets/images/male-student.png" alt="Ø£ÙŠÙ‚ÙˆÙ†Ø© Ø§Ù„Ø·Ø§Ù„Ø¨" class="student-icon"> <!-- ØªØ£ÙƒØ¯ Ù…Ù† Ø£Ù† Ù…Ø³Ø§Ø± Ø§Ù„ØµÙˆØ±Ø© ØµØ­ÙŠØ­ -->

            <h2>ØªØ³Ø¬ÙŠÙ„ Ø§Ù„Ø¯Ø®ÙˆÙ„</h2>

            <!-- Ø¹Ø±Ø¶ Ø±Ø³Ø§Ù„Ø© Ø§Ù„Ø®Ø·Ø£ Ø¥Ø°Ø§ ÙˆØ¬Ø¯Øª -->
            <?php if (isset($error) && $error != '') { echo "<p class='error'>$error</p>"; } ?>

            <!-- Ù†Ù…ÙˆØ°Ø¬ ØªØ³Ø¬ÙŠÙ„ Ø§Ù„Ø¯Ø®ÙˆÙ„ -->
            <form method="POST" action="">
                <input type="email" name="email" placeholder="Ø§Ù„Ø¨Ø±ÙŠØ¯ Ø§Ù„Ø¥Ù„ÙƒØªØ±ÙˆÙ†ÙŠ" required>
                <input type="password" name="password" placeholder="ÙƒÙ„Ù…Ø© Ø§Ù„Ù…Ø±ÙˆØ±" required>
                <button type="submit">ØªØ³Ø¬ÙŠÙ„ Ø§Ù„Ø¯Ø®ÙˆÙ„</button>
            </form>

            <!-- Ø±Ø§Ø¨Ø· Ù†Ø³ÙŠØª ÙƒÙ„Ù…Ø© Ø§Ù„Ù…Ø±ÙˆØ± -->
            <a href="forgot_password.php">Ù†Ø³ÙŠØª ÙƒÙ„Ù…Ø© Ø§Ù„Ù…Ø±ÙˆØ±ØŸ</a>
        </div>
    </div>

    <!-- Ø§Ù„ÙÙˆØªØ± (Footer) -->
    <footer>
     
        <p>&copy; 2024 Ø¨ÙˆØ§Ø¨Ø© ØªØ³Ù‡ÙŠÙ„. Ø¬Ù…ÙŠØ¹ Ø§Ù„Ø­Ù‚ÙˆÙ‚ Ù…Ø­ÙÙˆØ¸Ø©.</p>
    </footer>

</body>
</html>
