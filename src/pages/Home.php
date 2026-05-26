<?php
session_start();
// Ø§ÙØªØ±Ø¶ Ø£Ù†Ùƒ ØªØ­ØµÙ„ Ø¹Ù„Ù‰ Ø§Ø³Ù… Ø§Ù„Ù…Ø³ØªØ®Ø¯Ù… Ù…Ù† ØªØ³Ø¬ÙŠÙ„ Ø§Ù„Ø¯Ø®ÙˆÙ„ Ø£Ùˆ Ù…ØµØ¯Ø± Ø¢Ø®Ø±
$name = ''; // ØªØ£ÙƒØ¯ Ù…Ù† ØªØ¹ÙŠÙŠÙ† Ø§Ù„Ù…ØªØºÙŠØ± $name Ø¨Ù‚ÙŠÙ…Ø© ØµØ­ÙŠØ­Ø©

// ØªØ­Ù‚Ù‚ Ù…Ù† Ø£Ù† Ø§Ù„Ù…ØªØºÙŠØ± $name ÙŠØ­ØªÙˆÙŠ Ø¹Ù„Ù‰ Ù‚ÙŠÙ…Ø© Ù‚Ø¨Ù„ Ø¥Ø¶Ø§ÙØªÙ‡ Ø¥Ù„Ù‰ Ø§Ù„Ø¬Ù„Ø³Ø©
if (!empty($name)) {
    $_SESSION['name'] = $name;
} else {
    // ÙŠÙ…ÙƒÙ†Ùƒ Ø¥Ø¹Ø§Ø¯Ø© ØªÙˆØ¬ÙŠÙ‡ Ø§Ù„Ù…Ø³ØªØ®Ø¯Ù… Ø¥Ù„Ù‰ ØµÙØ­Ø© ØªØ³Ø¬ÙŠÙ„ Ø§Ù„Ø¯Ø®ÙˆÙ„ Ø¥Ø°Ø§ Ù„Ù… ÙŠÙƒÙ† Ù…Ø³Ø¬Ù„Ø§Ù‹
    // header("Location: login.php");
    // exit();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ù„ÙˆØ­Ø© ØªØ­ÙƒÙ… Ø§Ù„Ø·Ø§Ù„Ø¨</title>
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
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #f1f5f9;
            background-image: url('../assets/images/BG-login.png');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            direction: rtl;
        }

        /* ØªÙ†Ø³ÙŠÙ‚ Ø§Ù„Ù‡ÙŠØ¯Ø± */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background-color: rgba(0, 0, 0, 0.7);
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

        /* ØªÙ†Ø³ÙŠÙ‚ Ù…Ø­ØªÙˆÙ‰ Ø§Ù„ØµÙØ­Ø© */
        .content-wrapper {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 100px 20px 20px; /* 100px Ù„Ù…Ù†Ø¹ Ø§Ù„Ù…Ø­ØªÙˆÙ‰ Ù…Ù† Ø§Ù„Ø§Ø®ØªØ¨Ø§Ø¡ Ø®Ù„Ù Ø§Ù„Ù‡ÙŠØ¯Ø± */
        }

        .dashboard-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 20px;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            max-width: 800px;
            width: 100%;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.05);
        }

        .dashboard-title {
            font-size: 32px;
            color: #264653;
            margin-bottom: 40px;
            text-align: center;
        }

        .buttons-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            width: 100%;
        }

        .square-btn {
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #2a9d8f;
            color: white;
            padding: 20px;
            width: 150px;
            height: 150px;
            text-align: center;
            border-radius: 15px;
            font-weight: bold;
            font-size: 18px;
            text-decoration: none;
            transition: background-color 0.3s ease, transform 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .square-btn:hover {
            background-color: #21867a;
            transform: translateY(-5px);
        }

        /* ØªÙ†Ø³ÙŠÙ‚ Ø§Ù„ÙÙˆØªØ± */
        footer {
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 20px 40px;
            text-align: center;
            width: 100%;
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

            .dashboard-title {
                font-size: 28px;
            }

            .square-btn {
                width: 120px;
                height: 120px;
                font-size: 16px;
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
    <!-- Navbar Section -->
    <nav class="navbar">
        <?php
        if (isset($_SESSION['name'])) {
            echo "<div class='nav-logo'> Ù…Ø±Ø­Ø¨Ø§Ù‹ØŒ " . htmlspecialchars($_SESSION['name']) . "</div>";
        } else {
            echo "<div class='nav-logo'> Ù…Ø±Ø­Ø¨Ø§Ù‹ Ø¨Ø§Ù„Ø·Ø§Ù„Ø¨</div>";
        }
        ?>
        <button class="logout-btn" onclick="window.location.href='../php/logout.php'">ØªØ³Ø¬ÙŠÙ„ Ø§Ù„Ø®Ø±ÙˆØ¬</button>
    </nav>

    <!-- Main Content Wrapper -->
    <div class="content-wrapper">
        <!-- Main Dashboard Section -->
        <div class="dashboard-container">
            <h1 class="dashboard-title">Ø§Ø®ØªØ± Ø§Ù„Ø¹Ø°Ø± Ø§Ù„Ù…Ù†Ø§Ø³Ø¨ Ù„Ø¥Ø±Ø³Ø§Ù„ Ø·Ù„Ø¨</h1>
            <div class="buttons-grid">
                <a href="req_form.php" class="square-btn">ØªØªØ¨Ø¹ Ø§Ù„Ø·Ù„Ø¨</a>
                <a href="finalForm.php" class="square-btn">Ø¹Ø°Ø± Ø§Ù„Ø§Ø®ØªØ¨Ø§Ø± Ø§Ù„Ù†Ù‡Ø§Ø¦ÙŠ</a>
                <a href="lecture_excuse.php" class="square-btn">Ø£Ø¹Ø°Ø§Ø± Ø§Ù„Ù…Ø­Ø§Ø¶Ø±Ø§Øª Ø§Ù„ÙŠÙˆÙ…ÙŠØ©</a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>

        <p>&copy; 2024 Ø¨ÙˆØ§Ø¨Ø© ØªØ³Ù‡ÙŠÙ„. Ø¬Ù…ÙŠØ¹ Ø§Ù„Ø­Ù‚ÙˆÙ‚ Ù…Ø­ÙÙˆØ¸Ø©.</p>
    </footer>
</body>
</html>

