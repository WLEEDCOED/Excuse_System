<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ø¨ÙˆØ§Ø¨Ø© Ø§Ù„Ø·Ø§Ù„Ø¨ - ØªØ³ØªÙ‡ÙŠÙ„</title>
    <!-- Ø¥Ø¶Ø§ÙØ© Ø®Ø·ÙˆØ· Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* Ø¥Ø¹Ø§Ø¯Ø© ØªØ¹ÙŠÙŠÙ† Ø¨Ø¹Ø¶ Ø§Ù„Ø£Ù†Ù…Ø§Ø· Ø§Ù„Ø§ÙØªØ±Ø§Ø¶ÙŠØ© */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Cairo', sans-serif;
        }

        /* ØªÙ†Ø³ÙŠÙ‚ Ø§Ù„Ø±Ø£Ø³ */
        header {
            background-color: #ffffff;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        header .logo img {
            height: 50px;
        }

        nav ul {
            display: flex;
            list-style: none;
        }

        nav ul li {
            margin: 0 15px;
        }

        nav ul li a {
            text-decoration: none;
            color: #333;
            font-size: 16px;
            position: relative;
            transition: color 0.3s ease;
        }

        nav ul li a:hover {
            color: #2a9d8f;
        }

        nav ul li a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            right: 0;
            width: 0%;
            height: 2px;
            background-color: #2a9d8f;
            transition: width 0.3s ease;
        }

        nav ul li a:hover::after {
            width: 100%;
            left: 0;
            right: auto;
        }

        .login-button {
            background-color: #2a9d8f;
            color: #fff;
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-size: 16px;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .login-button:hover {
            background-color: #21867a;
            transform: translateY(-2px);
        }

        /* Ø§Ù„Ù‚Ø³Ù… Ø§Ù„Ø±Ø¦ÙŠØ³ÙŠ */
        #main {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding-top: 100px; /* Ù„Ù…Ù†Ø¹ Ø§Ù„Ù…Ø­ØªÙˆÙ‰ Ù…Ù† Ø§Ù„Ø§Ø®ØªØ¨Ø§Ø¡ Ø®Ù„Ù Ø§Ù„Ø±Ø£Ø³ Ø§Ù„Ø«Ø§Ø¨Øª */
            background-image: url('../assets/images/BG-login.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .content {
            background-color: rgba(255, 255, 255, 0.8);
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .content h1 {
            font-size: 48px;
            margin-bottom: 20px;
            color: #2c7bb6;
        }

        .content p {
            font-size: 20px;
            margin-bottom: 30px;
            color: #555;
        }

        .buttons {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .buttons .btn {
            background-color: #2a9d8f;
            color: #fff;
            padding: 15px 30px;
            border-radius: 30px;
            text-decoration: none;
            font-size: 18px;
            transition: background-color 0.3s ease, transform 0.3s ease;
            display: inline-block;
        }

        .buttons .btn:hover {
            background-color: #21867a;
            transform: translateY(-3px);
        }

        /* ØªØ°ÙŠÙŠÙ„ Ø§Ù„ØµÙØ­Ø© */
        footer {
            text-align: center;
            padding: 20px;
            background-color: #ffffff;
            color: #555;
            font-size: 14px;
            border-top: 1px solid #ddd;
            position: relative;
            width: 100%;
        }

        /* ØªØµÙ…ÙŠÙ… Ù…ØªØ¬Ø§ÙˆØ¨ */
        @media (max-width: 768px) {
            header {
                flex-direction: column;
                align-items: flex-start;
            }

            nav ul {
                flex-direction: column;
                margin-top: 10px;
            }

            nav ul li {
                margin: 10px 0;
            }

            .content h1 {
                font-size: 36px;
            }

            .content p {
                font-size: 18px;
            }

            .buttons {
                flex-direction: column;
                gap: 10px;
            }

            .buttons .btn {
                padding: 12px 25px;
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <!-- Ø§Ù„Ø±Ø£Ø³ -->
    <header>
        <a href="#" class="logo">
            <img src="../assets/images/male-student.png" alt="Logo" />
        </a>
        <nav>
            <ul>
                <li><a href="#about">Ø¹Ù† Ø§Ù„Ù…ÙˆÙ‚Ø¹</a></li>
                <li><a href="#contact">ØªÙˆØ§ØµÙ„ Ù…Ø¹Ù†Ø§</a></li>
            </ul>
        </nav>
        <a href="register.php" class="login-button">Ø¥Ù†Ø´Ø§Ø¡ Ø­Ø³Ø§Ø¨ Ø·Ø§Ù„Ø¨</a>
    </header>

    <!-- Ø§Ù„Ù‚Ø³Ù… Ø§Ù„Ø±Ø¦ÙŠØ³ÙŠ -->
    <section id="main">
        <div class="content">
            <h1>Ù…Ø±Ø­Ø¨Ø§Ù‹ Ø¨Ùƒ ÙÙŠ Ø¨ÙˆØ§Ø¨Ø© ØªØ³Ù‡ÙŠÙ„</h1>
            <p>Ø£Ø±Ø³Ù„ ÙˆØªØªØ¨Ø¹ Ø·Ù„Ø¨Ø§Øª Ø£Ø¹Ø°Ø§Ø±Ùƒ Ø¨Ø³Ù‡ÙˆÙ„Ø© ÙˆØ³Ø±Ø¹Ø©</p>
            <div class="buttons">
                <a href="login.php" class="btn btn-student">ØªØ³Ø¬ÙŠÙ„ Ø§Ù„Ø¯Ø®ÙˆÙ„ ÙƒØ·Ø§Ù„Ø¨ </a>
                <!-- Ø§Ù„Ø²Ø± Ø§Ù„Ø¬Ø¯ÙŠØ¯ Ù„ØªØ³Ø¬ÙŠÙ„ ÙƒØ¹Ø¶Ùˆ ØªØ¯Ø±ÙŠØ³ -->
                <a href="loginp.php" class="btn btn-teacher"> ØªØ³Ø¬ÙŠÙ„ Ø§Ù„Ø¯Ø®ÙˆÙ„ ÙƒØ¹Ø¶Ùˆ ØªØ¯Ø±ÙŠØ³</a>
            </div>
        </div>
    </section>

    <!-- ØªØ°ÙŠÙŠÙ„ Ø§Ù„ØµÙØ­Ø© -->
    <footer>
        <p>&copy; 2024 Ø¨ÙˆØ§Ø¨Ø© ØªØ³Ù‡ÙŠÙ„. Ø¬Ù…ÙŠØ¹ Ø§Ù„Ø­Ù‚ÙˆÙ‚ Ù…Ø­ÙÙˆØ¸Ø©.</p>
    </footer>
</body>
</html>

