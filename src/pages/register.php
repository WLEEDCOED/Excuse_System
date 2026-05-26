<?php
session_start();
include '../includes/db_connection.php';

// Ø§Ù„ØªØ­Ù‚Ù‚ Ù…Ù† Ø£Ù† Ø§Ù„Ù†Ù…ÙˆØ°Ø¬ ØªÙ… Ø¥Ø±Ø³Ø§Ù„Ù‡
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = trim($_POST['id']);  // Ø§Ø³ØªÙ„Ø§Ù… id Ù…Ù† Ø§Ù„Ù†Ù…ÙˆØ°Ø¬
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $department_id = $_POST['department']; // Ø§Ø³ØªÙ„Ø§Ù… department_id Ù…Ù† Ø§Ù„Ù†Ù…ÙˆØ°Ø¬
    $role_id = 3; // 3 ÙŠÙ…Ø«Ù„ Ø§Ù„Ø¯ÙˆØ± "student" ÙÙŠ Ø¬Ø¯ÙˆÙ„ roles

    // Ø§Ù„ØªØ­Ù‚Ù‚ Ù…Ù† ØµØ­Ø© Ø¨ÙŠØ§Ù†Ø§Øª Ø§Ù„Ù‚Ø³Ù…
    $valid_departments = [1, 2, 3];
    if (!in_array($department_id, $valid_departments)) {
        $error_message = "Ø§Ù„Ù‚Ø³Ù… Ø§Ù„Ù…Ø­Ø¯Ø¯ ØºÙŠØ± ØµØ§Ù„Ø­.";
    } else {
        // ØªØ´ÙÙŠØ± ÙƒÙ„Ù…Ø© Ø§Ù„Ù…Ø±ÙˆØ± Ø¨Ø§Ø³ØªØ®Ø¯Ø§Ù… password_hash
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Ø¨Ø¯Ø¡ Ø§Ù„Ù…Ø¹Ø§Ù…Ù„Ø© (transaction)
        $conn->begin_transaction();

        try {
            // ØªØ­Ø¶ÙŠØ± Ø§Ù„Ø§Ø³ØªØ¹Ù„Ø§Ù…
            $stmt = $conn->prepare("INSERT INTO students (id, name, email, password, role_id, department_id) VALUES (?, ?, ?, ?, ?, ?)");

            // Ø§Ù„ØªØ­Ù‚Ù‚ Ù…Ù…Ø§ Ø¥Ø°Ø§ ÙƒØ§Ù† `prepare` Ù†Ø§Ø¬Ø­Ù‹Ø§
            if (!$stmt) {
                throw new Exception("Ø®Ø·Ø£ ÙÙŠ Ø§Ù„Ø§Ø³ØªØ¹Ù„Ø§Ù…: " . $conn->error);
            }

            // Ø±Ø¨Ø· Ø§Ù„Ù…ØªØºÙŠØ±Ø§Øª Ù…Ø¹ Ø§Ù„Ø§Ø³ØªØ¹Ù„Ø§Ù…
            $stmt->bind_param("isssii", $id, $name, $email, $hashed_password, $role_id, $department_id);

            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }

            // ØªØ£ÙƒÙŠØ¯ Ø§Ù„ØªØºÙŠÙŠØ±Ø§Øª Ø¥Ø°Ø§ ØªÙ… Ø§Ù„ØªÙ†ÙÙŠØ° Ø¨Ù†Ø¬Ø§Ø­
            $conn->commit();
            $success_message = "ØªÙ… Ø¥Ù†Ø´Ø§Ø¡ Ø§Ù„Ø­Ø³Ø§Ø¨ Ø¨Ù†Ø¬Ø§Ø­. ÙŠÙ…ÙƒÙ†Ùƒ Ø§Ù„Ø¢Ù† ØªØ³Ø¬ÙŠÙ„ Ø§Ù„Ø¯Ø®ÙˆÙ„.";
            
            // Ø¥Ø¹Ø§Ø¯Ø© ØªÙˆØ¬ÙŠÙ‡ Ø§Ù„Ù…Ø³ØªØ®Ø¯Ù… Ø¥Ù„Ù‰ ØµÙØ­Ø© ØªØ³Ø¬ÙŠÙ„ Ø§Ù„Ø¯Ø®ÙˆÙ„ Ø¨Ø¹Ø¯ Ù†Ø¬Ø§Ø­ Ø§Ù„ØªØ³Ø¬ÙŠÙ„ (Ø§Ø®ØªÙŠØ§Ø±ÙŠ)
            // header("Location: login.php?message=success");
            // exit();

        } catch (Exception $e) {
            // Ø¥Ù„ØºØ§Ø¡ Ø§Ù„Ù…Ø¹Ø§Ù…Ù„Ø© ÙÙŠ Ø­Ø§Ù„ Ø­Ø¯ÙˆØ« Ø®Ø·Ø£
            $conn->rollback();
            $error_message = "Ø­Ø¯Ø« Ø®Ø·Ø£: " . $e->getMessage();
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
    <title>ØªØ³Ø¬ÙŠÙ„ Ø­Ø³Ø§Ø¨ Ø·Ø§Ù„Ø¨ Ø¬Ø¯ÙŠØ¯</title>
    <!-- Ø¥Ø¶Ø§ÙØ© Ø®Ø· Cairo Ù…Ù† Google Fonts -->
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
            background-color: #f4f7f6;
            background-image: url('../assets/images/BG-login.png');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            direction: rtl;
            color: #333;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ØªÙ†Ø³ÙŠÙ‚ Ø§Ù„Ù‡ÙŠØ¯Ø± */
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

        /* ØªÙ†Ø³ÙŠÙ‚ Ù…Ø­ØªÙˆÙ‰ Ø§Ù„ØµÙØ­Ø© */
        .container {
            max-width: 500px;
            margin: 100px auto 40px; /* Ø²ÙŠØ§Ø¯Ø© Ø§Ù„Ù…Ø³Ø§ÙØ© Ø§Ù„Ø¹Ù„ÙˆÙŠØ© Ù„Ù…Ù†Ø¹ Ø§Ù„Ø§Ø®ØªØ¨Ø§Ø¡ Ø®Ù„Ù Ø§Ù„Ù‡ÙŠØ¯Ø± */
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

        /* ØªÙ†Ø³ÙŠÙ‚ Ø§Ù„ÙÙˆØ±Ù… */
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

        /* ØªÙ†Ø³ÙŠÙ‚ Ø²Ø± Ø§Ù„Ø¥Ø±Ø³Ø§Ù„ */
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

        /* Ù†ØµØ§Ø¦Ø­ ÙˆØªØ¹Ù„ÙŠÙ‚Ø§Øª Ø§Ù„Ù…Ø³ØªØ®Ø¯Ù… */
        input::placeholder,
        select option[value=""] {
            color: #aaa;
            font-size: 14px;
        }

        /* ØªØµÙ…ÙŠÙ… Ø§Ù„Ø±ÙˆØ§Ø¨Ø· Ø§Ù„Ø®Ø§ØµØ© Ø¨ØµÙØ­Ø© "Ù†Ø³ÙŠØª ÙƒÙ„Ù…Ø© Ø§Ù„Ù…Ø±ÙˆØ±" Ùˆ "Ù„Ø¯ÙŠ Ø­Ø³Ø§Ø¨" */
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

        /* Ø±Ø³Ø§Ù„Ø© Ø§Ù„Ù†Ø¬Ø§Ø­ ÙˆØ§Ù„Ø®Ø·Ø£ */
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

        /* Ø§Ù„ÙÙˆØªØ± */
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

        /* ØªØµÙ…ÙŠÙ… Ù…ØªØ¬Ø§ÙˆØ¨ */
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
    <!-- Ø¥Ø¶Ø§ÙØ© JavaScript Ù„Ù„ØªØ­Ù‚Ù‚ Ù…Ù† ØµØ­Ø© Ø§Ù„Ø¨Ø±ÙŠØ¯ Ø§Ù„Ø¥Ù„ÙƒØªØ±ÙˆÙ†ÙŠ -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.querySelector("form");
            form.addEventListener("submit", function(event) {
                const email = form.email.value;
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(email)) {
                    alert("ÙŠØ±Ø¬Ù‰ Ø¥Ø¯Ø®Ø§Ù„ Ø¨Ø±ÙŠØ¯ Ø¥Ù„ÙƒØªØ±ÙˆÙ†ÙŠ ØµØ§Ù„Ø­.");
                    event.preventDefault();
                }

                // Ø§Ù„ØªØ­Ù‚Ù‚ Ù…Ù† Ø§Ø®ØªÙŠØ§Ø± Ù‚Ø³Ù…
                const department = form.department.value;
                if (department === "") {
                    alert("ÙŠØ±Ø¬Ù‰ Ø§Ø®ØªÙŠØ§Ø± Ù‚Ø³Ù….");
                    event.preventDefault();
                }
            });
        });
    </script>
</head>
<body>

    <!-- Ø§Ù„Ù‡ÙŠØ¯Ø± (Header) -->
    <header class="header navbar">
        <?php echo "<div class='nav-logo'>Ø¨ÙˆØ§Ø¨Ø© ØªØ³Ù‡ÙŠÙ„</div>"; ?>
        <div class="nav">
            <a href="login.php">ØªØ³Ø¬ÙŠÙ„ Ø§Ù„Ø¯Ø®ÙˆÙ„</a>
        </div>
    </header>

    <!-- Ù…Ø­ØªÙˆÙ‰ Ø§Ù„ØµÙØ­Ø© -->
    <div class="container">
        <h2>ØªØ³Ø¬ÙŠÙ„ Ø­Ø³Ø§Ø¨ Ø·Ø§Ù„Ø¨ Ø¬Ø¯ÙŠØ¯</h2>
        <!-- Ø¹Ø±Ø¶ Ø±Ø³Ø§Ù„Ø© Ø§Ù„Ù†Ø¬Ø§Ø­ Ø£Ùˆ Ø§Ù„Ø®Ø·Ø£ -->
        <?php 
        if (isset($success_message)) {
            echo "<div class='message success-message'>$success_message</div>";
        }
        if (isset($error_message)) {
            echo "<div class='message error-message'>$error_message</div>";
        }
        ?>
        <form action="register.php" method="post">
            <input type="text" name="name" placeholder="Ø§Ù„Ø§Ø³Ù… Ø§Ù„ÙƒØ§Ù…Ù„" required>
            <input type="text" name="id" placeholder="Ø±Ù‚Ù… Ø§Ù„Ø¬Ø§Ù…Ø¹ÙŠ" required>
            <input type="email" name="email" placeholder="Ø§Ù„Ø¨Ø±ÙŠØ¯ Ø§Ù„Ø¥Ù„ÙƒØªØ±ÙˆÙ†ÙŠ" required>
            <input type="password" name="password" placeholder="ÙƒÙ„Ù…Ø© Ø§Ù„Ù…Ø±ÙˆØ±" required>
            
            <!-- Ø¥Ø¶Ø§ÙØ© Ù‚Ø§Ø¦Ù…Ø© Ø§Ù„Ø£Ù‚Ø³Ø§Ù… -->
            <select name="department" required>
                <option value="">Ø§Ø®ØªØ± Ø§Ù„Ù‚Ø³Ù…</option>
                <option value="1">Ø¹Ù„ÙˆÙ… Ø§Ù„Ø­Ø§Ø³Ø¨</option>
                <option value="2">ØªÙ‚Ù†ÙŠØ© Ø§Ù„Ù…Ø¹Ù„ÙˆÙ…Ø§Øª</option>
                <option value="3">Ù†Ø¸Ù… Ø§Ù„Ù…Ø¹Ù„ÙˆÙ…Ø§Øª</option>
            </select>

            <input type="submit" value="ØªØ³Ø¬ÙŠÙ„">

            <!-- Ø±ÙˆØ§Ø¨Ø· Ø¥Ø¶Ø§ÙÙŠØ© -->
            <div class="links">
                <a href="forgot_password.php">Ù†Ø³ÙŠØª ÙƒÙ„Ù…Ø© Ø§Ù„Ù…Ø±ÙˆØ±ØŸ</a>
                <a href="login.php">Ù„Ø¯ÙŠÙƒ Ø­Ø³Ø§Ø¨ Ø¨Ø§Ù„ÙØ¹Ù„ØŸ ØªØ³Ø¬ÙŠÙ„ Ø§Ù„Ø¯Ø®ÙˆÙ„</a>
            </div>
        </form>
    </div>

    <!-- Ø§Ù„ÙÙˆØªØ± (Footer) -->
    <footer>
        <a href="#">Ø­ÙˆÙ„</a>
        <a href="#">ØªÙˆØ§ØµÙ„ Ù…Ø¹Ù†Ø§</a>
        <p>&copy; 2024 Ø¨ÙˆØ§Ø¨Ø© ØªØ³Ù‡ÙŠÙ„. Ø¬Ù…ÙŠØ¹ Ø§Ù„Ø­Ù‚ÙˆÙ‚ Ù…Ø­ÙÙˆØ¸Ø©.</p>
    </footer>

</body>
</html>

