<?php
session_start();
include '../includes/db_connection.php';

// Ø§Ù„ØªØ£ÙƒØ¯ Ù…Ù† ØªØ³Ø¬ÙŠÙ„ Ø§Ù„Ø¯Ø®ÙˆÙ„ ÙƒØ·Ø§Ù„Ø¨
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != '3') {
    header('Location: login.php');
    exit;
}

// Ø¬Ù„Ø¨ Ù…Ø¹Ø±Ù Ø§Ù„Ø·Ø§Ù„Ø¨ Ù…Ù† Ø§Ù„Ø¬Ù„Ø³Ø©
$student_id = $_SESSION['user_id'];

// Ø¬Ù„Ø¨ Ø¬Ù…ÙŠØ¹ Ø§Ù„Ø·Ù„Ø¨Ø§Øª Ø§Ù„Ù†Ù‡Ø§Ø¦ÙŠØ© Ø§Ù„Ù…Ù‚Ø¯Ù…Ø© Ù…Ù† Ø§Ù„Ø·Ø§Ù„Ø¨
$sql_final_requests = "SELECT * FROM final_exam WHERE student_id = ? ORDER BY id ASC";
$stmt_final_requests = $conn->prepare($sql_final_requests);
$stmt_final_requests->bind_param("i", $student_id);
$stmt_final_requests->execute();
$result_final_requests = $stmt_final_requests->get_result();

// Ø¬Ù„Ø¨ Ø·Ù„Ø¨Ø§Øª Ø§Ù„Ø£Ø¹Ø°Ø§Ø± Ø§Ù„ÙŠÙˆÙ…ÙŠØ© Ù…Ù† Ø¬Ø¯ÙˆÙ„ 'lecture_excuses'
$sql_daily_excuses = "SELECT lecture_excuses.*, professors.name AS professor_name, course.name AS class_name
                      FROM lecture_excuses
                      JOIN professors ON lecture_excuses.professor_id = professors.id
                      JOIN course ON lecture_excuses.lectures_id = course.id
                      WHERE lecture_excuses.student_id = ?
                      ORDER BY lecture_excuses.id ASC";

$stmt_daily_excuses = $conn->prepare($sql_daily_excuses);
$stmt_daily_excuses->bind_param("i", $student_id);
$stmt_daily_excuses->execute();
$result_daily_excuses = $stmt_daily_excuses->get_result();


// Ø¬Ù„Ø¨ Ø¬Ù…ÙŠØ¹ Ø§Ù„Ù…Ø³Ø¤ÙˆÙ„ÙŠÙ† Ù…Ù† Ø¬Ø¯ÙˆÙ„ 'admins'
$admins_sql = "SELECT * FROM admins";
$admins_result = $conn->query($admins_sql);

// Ø­ÙØ¸ Ø¨ÙŠØ§Ù†Ø§Øª Ø§Ù„Ù…Ø³Ø¤ÙˆÙ„ÙŠÙ†
$admins = [];
while ($admin = $admins_result->fetch_assoc()) {
    $admins[$admin['id']] = $admin['name'];
}

// Ø¬Ù„Ø¨ Ø¬Ù…ÙŠØ¹ Ø§Ù„Ù…Ø³ØªØ®Ø¯Ù…ÙŠÙ† Ù…Ù† Ø¬Ø¯ÙˆÙ„ 'users'
$users_sql = "SELECT * FROM users";
$users_result = $conn->query($users_sql);

// Ø­ÙØ¸ Ø¨ÙŠØ§Ù†Ø§Øª Ø§Ù„Ù…Ø³ØªØ®Ø¯Ù…ÙŠÙ†
$users = [];
while ($user = $users_result->fetch_assoc()) {
    $users[$user['id']] = $user['name'];
}

// Ø§Ù„Ø¨Ø­Ø« Ø¹Ù† Ø·Ù„Ø¨ Ù…Ø¹ÙŠÙ† Ø¥Ø°Ø§ ØªÙ… Ø¥Ø¯Ø®Ø§Ù„ Ø±Ù‚Ù… Ø·Ù„Ø¨
if (isset($_POST['search_request_id'])) {
    $search_request_id = intval($_POST['search_request_id']);

    // Ø§Ø³ØªØ¹Ù„Ø§Ù… Ø§Ù„Ø¨Ø­Ø« Ø¹Ù† Ø§Ù„Ø·Ù„Ø¨ Ø§Ù„Ù†Ù‡Ø§Ø¦ÙŠ
    $search_sql = "SELECT * FROM final_exam WHERE student_id = ? AND id = ?";
    $search_stmt = $conn->prepare($search_sql);
    $search_stmt->bind_param("ii", $student_id, $search_request_id);
    $search_stmt->execute();
    $search_result = $search_stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ù„ÙˆØ­Ø© Ø§Ù„Ø·Ø§Ù„Ø¨ - Ø·Ù„Ø¨Ø§ØªÙŠ</title>
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
            background-color: #e0f7fa;
            background-image: url('../assets/images/BG-login.png');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            direction: rtl;
            color: #333;
        }

        /* ØªÙ†Ø³ÙŠÙ‚ Ø§Ù„Ù‡ÙŠØ¯Ø± */
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

        /* ØªÙ†Ø³ÙŠÙ‚ Ù…Ø­ØªÙˆÙ‰ Ø§Ù„ØµÙØ­Ø© */
        .dashboard-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 100px 20px 20px; /* 100px Ù„Ù…Ù†Ø¹ Ø§Ù„Ù…Ø­ØªÙˆÙ‰ Ù…Ù† Ø§Ù„Ø§Ø®ØªØ¨Ø§Ø¡ Ø®Ù„Ù Ø§Ù„Ù‡ÙŠØ¯Ø± Ø§Ù„Ø«Ø§Ø¨Øª */
            max-width: 1200px;
            margin: 0 auto;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #00796b;
        }

        /* Ù‚Ø§Ø¦Ù…Ø© Ø§Ù„ØªÙ†Ù‚Ù„ Ø¨ÙŠÙ† Ø§Ù„ØªØ¨ÙˆÙŠØ¨Ø§Øª */
        .nav-menu {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .nav-menu button {
            padding: 10px 20px;
            margin: 5px;
            background-color: #00796b;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .nav-menu button.active,
        .nav-menu button:hover {
            background-color: #004d40;
            transform: translateY(-2px);
        }

        /* ØªÙ†Ø³ÙŠÙ‚ Ø·Ù„Ø¨Ø§Øª Ø§Ù„Ø£Ø¹Ø°Ø§Ø± */
        .request {
            border: 1px solid #00796b;
            padding: 20px;
            margin: 15px 0;
            border-radius: 8px;
            background-color: #e0f2f1;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .request h3 {
            margin-bottom: 10px;
            color: #004d40;
        }

        .request p {
            margin: 5px 0;
            color: #004d40;
        }

        .request p.status-rejected {
            color: red;
        }

        .request p.status-approved-professor {
            color: green;
        }

        /* Ø§Ù„ÙÙˆØªØ± */
        footer {
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 20px 40px;
            text-align: center;
            width: 100%;
            margin-top: auto;
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

            .dashboard-container {
                padding: 80px 20px 20px;
            }

            .nav-menu button {
                width: 100%;
                text-align: center;
            }

            .request {
                padding: 15px;
            }

            footer {
                padding: 15px 20px;
            }

            footer a {
                margin: 0 5px;
                font-size: 14px;
            }
        }

        /* ØªØ­Ø³ÙŠÙ†Ø§Øª Ø¥Ø¶Ø§ÙÙŠØ© */
        .container {
            display: none;
        }

        .container.active {
            display: block;
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
    <div class="dashboard-container">
        <h2>Ø·Ù„Ø¨Ø§ØªÙŠ</h2>

        <!-- Ù‚Ø§Ø¦Ù…Ø© Ø§Ù„ØªÙ†Ù‚Ù„ Ø¨ÙŠÙ† Ø§Ù„ØªØ¨ÙˆÙŠØ¨Ø§Øª -->
        <div class="nav-menu">
            <button class="tab-link active" onclick="openTab('final-requests-container')">Ø·Ù„Ø¨Ø§Øª Ø§Ù„Ø£Ø¹Ø°Ø§Ø± Ø§Ù„Ù†Ù‡Ø§Ø¦ÙŠØ©</button>
            <button class="tab-link" onclick="openTab('daily-requests-container')">Ø§Ù„Ø·Ù„Ø¨Ø§Øª Ø§Ù„ÙŠÙˆÙ…ÙŠØ©</button>
        </div>

        <!-- Ø¹Ø±Ø¶ Ø·Ù„Ø¨Ø§Øª Ø§Ù„Ø£Ø¹Ø°Ø§Ø± Ø§Ù„Ù†Ù‡Ø§Ø¦ÙŠØ© -->
        <div id="final-requests-container" class="container active">
            <h3>Ø·Ù„Ø¨Ø§Øª Ø§Ù„Ø£Ø¹Ø°Ø§Ø± Ø§Ù„Ù†Ù‡Ø§Ø¦ÙŠØ©</h3>
            <?php
            if ($result_final_requests->num_rows > 0) {
                while ($row = $result_final_requests->fetch_assoc()) {
                    echo "<div class='request' id='request-" . $row['id'] . "'>";
                    echo "<h3>Ø·Ù„Ø¨ Ø±Ù‚Ù…: " . $row['id'] . "</h3>";

                    // Ø¹Ø±Ø¶ Ø­Ø§Ù„Ø© Ø§Ù„Ø·Ù„Ø¨ ÙˆØªØ§Ø±ÙŠØ® Ø§Ù„Ø§Ø®ØªØ¨Ø§Ø± Ø¥Ø°Ø§ ØªÙ… ØªØ­Ø¯ÙŠØ¯Ù‡
                    if ($row['status'] == 'approved') {
                        if (!empty($row['exam_date']) && $row['exam_date'] != '0000-00-00') {
                            $formatted_date = date('Y-m-d', strtotime($row['exam_date']));
                            echo "<p>ØªÙ… Ù‚Ø¨ÙˆÙ„ Ø·Ù„Ø¨Ùƒ. Ù…ÙˆØ¹Ø¯ Ø§Ù„Ø§Ø®ØªØ¨Ø§Ø± Ù‡Ùˆ: " . $formatted_date . "</p>";
                        } else {
                            echo "<p>ØªÙ… Ù‚Ø¨ÙˆÙ„ Ø·Ù„Ø¨Ùƒ. Ù…ÙˆØ¹Ø¯ Ø§Ù„Ø§Ø®ØªØ¨Ø§Ø± Ø³ÙŠØªÙ… ØªØ­Ø¯ÙŠØ¯Ù‡ Ù‚Ø±ÙŠØ¨Ù‹Ø§.</p>";
                        }
                    } elseif ($row['status'] == 'rejected') {
                        echo "<p class='status-rejected'>ØªÙ… Ø±ÙØ¶ Ø·Ù„Ø¨Ùƒ.</p>";

                        if (!empty($row['rejection_reason'])) {
                            echo "<p>Ø³Ø¨Ø¨ Ø§Ù„Ø±ÙØ¶: " . htmlspecialchars($row['rejection_reason']) . "</p>";
                        }
                    } elseif ($row['status'] === 'Under_Progress') {
                        echo "<p>Ø§Ù„Ø­Ø§Ù„Ø©: Ù‚ÙŠØ¯ Ø§Ù„Ù…Ø±Ø§Ø¬Ø¹Ø© Ù…Ù† Ù…Ø¬Ù„Ø³ Ø§Ù„ÙƒÙ„ÙŠØ©</p>";
                    } else {
                        echo "<p>Ø§Ù„Ø­Ø§Ù„Ø©: " . htmlspecialchars($row['status']) . "</p>";
                    }

                    // Ø¹Ø±Ø¶ Ø§Ø³Ù… Ø§Ù„Ù…Ø³Ø¤ÙˆÙ„ Ø§Ù„Ø£ÙˆÙ„ Ø¥Ø°Ø§ ÙƒØ§Ù† Ø§Ù„Ø·Ù„Ø¨ Ø¹Ù†Ø¯Ù‡
                    if ($row['current_level'] == 1 && isset($users[$row['current_admin']])) {
                        echo "<p>Ø§Ù„Ø·Ù„Ø¨ Ø¹Ù†Ø¯: " . htmlspecialchars($users[$row['current_admin']]) . " </p>";
                    }
                    // Ø¹Ø±Ø¶ Ø§Ø³Ù… Ø§Ù„Ù…Ø³Ø¤ÙˆÙ„ Ø§Ù„Ø«Ø§Ù†ÙŠ Ø¥Ø°Ø§ ÙƒØ§Ù† Ø§Ù„Ø·Ù„Ø¨ Ø¹Ù†Ø¯Ù‡
                    elseif ($row['current_level'] == 2 && isset($admins[$row['current_admin']])) {
                        echo "<p>Ø§Ù„Ø·Ù„Ø¨ Ø¹Ù†Ø¯: " . htmlspecialchars($admins[$row['current_admin']]) . " </p>";
                    }

                    echo "</div>";
                }
            } else {
                echo "<p>Ù„Ø§ ØªÙˆØ¬Ø¯ Ø·Ù„Ø¨Ø§Øª Ø­ØªÙ‰ Ø§Ù„Ø¢Ù†.</p>";
            }
            ?>
        </div>

        <!-- Ø¹Ø±Ø¶ Ø·Ù„Ø¨Ø§Øª Ø§Ù„Ø£Ø¹Ø°Ø§Ø± Ø§Ù„ÙŠÙˆÙ…ÙŠØ© -->
        <div id="daily-requests-container" class="container">
            <h3>Ø§Ù„Ø·Ù„Ø¨Ø§Øª Ø§Ù„ÙŠÙˆÙ…ÙŠØ©</h3>
            <?php
            if ($result_daily_excuses->num_rows > 0) {
                while ($row = $result_daily_excuses->fetch_assoc()) {
                    echo "<div class='request' id='daily-request-" . $row['id'] . "'>";
                    echo "<h3> Ø§Ù„Ø§Ø¹Ø°Ø§Ø± Ø§Ù„ÙŠÙˆÙ…ÙŠÙ‡ : " . $row['id'] . "</h3>";
                    echo "<p>Ø§Ù„Ù…Ø§Ø¯Ø©: " . htmlspecialchars($row['class_name']) . "</p>";
                    echo "<p>Ø¯ÙƒØªÙˆØ± Ø§Ù„Ù…Ø§Ø¯Ù‡: " . htmlspecialchars($row['professor_name']) . "</p>";
                    echo "<p>Ø§Ù„ÙˆØµÙ: " . htmlspecialchars($row['description']) . "</p>";
                    echo "<p>ØªØ§Ø±ÙŠØ® Ø§Ù„ØºÙŠØ§Ø¨: " . htmlspecialchars($row['absence_date']) . "</p>";
                    echo "<p>Ø±Ù‚Ù… Ø§Ù„Ø´Ø¹Ø¨Ø©: " . htmlspecialchars($row['section_number']) . "</p>";
                    echo "<p>Ø­Ø§Ù„Ø© Ø§Ù„Ø·Ù„Ø¨: " . htmlspecialchars($row['status']) . "</p>";
                    echo "</div>";
                }
            } else {
                echo "<p>Ù„Ø§ ØªÙˆØ¬Ø¯ Ø·Ù„Ø¨Ø§Øª ÙŠÙˆÙ…ÙŠØ© Ø­ØªÙ‰ Ø§Ù„Ø¢Ù†.</p>";
            }
            ?>
        </div>

    </div>

    <!-- Footer -->
    <footer>
     
        <p>&copy; 2024 Ø¨ÙˆØ§Ø¨Ø© ØªØ³Ù‡ÙŠÙ„. Ø¬Ù…ÙŠØ¹ Ø§Ù„Ø­Ù‚ÙˆÙ‚ Ù…Ø­ÙÙˆØ¸Ø©.</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Ø¯Ø§Ù„Ø© Ù„ØªØ¨Ø¯ÙŠÙ„ Ø§Ù„Ø¹Ø±Ø¶ Ø¨ÙŠÙ† Ø§Ù„Ø·Ù„Ø¨Ø§Øª
        function openTab(tabId) {
            $('.container').removeClass('active');
            $('#' + tabId).addClass('active');

            $('.tab-link').removeClass('active');
            $('[onclick="openTab(\'' + tabId + '\')"]').addClass('active');
        }

        // Ø¯Ø§Ù„Ø© Ø§Ù„Ø¨Ø­Ø« Ø¨Ø§Ø³ØªØ®Ø¯Ø§Ù… AJAX (ØªØ­ØªØ§Ø¬ Ø¥Ù„Ù‰ ØµÙØ­Ø© '../php/search_request.php' Ù„Ù„ØªØ¹Ø§Ù…Ù„ Ù…Ø¹ Ø§Ù„Ø·Ù„Ø¨)
        function searchRequest() {
            var requestId = $('#searchRequestId').val();

            if (requestId) {
                $.ajax({
                    url: '../php/search_request.php',
                    type: 'POST',
                    data: { request_id: requestId },
                    success: function(response) {
                        $('#requests-container').html(response);
                    }
                });
            } else {
                alert('ÙŠØ±Ø¬Ù‰ Ø¥Ø¯Ø®Ø§Ù„ Ø±Ù‚Ù… Ø§Ù„Ø·Ù„Ø¨ Ù„Ù„Ø¨Ø­Ø«.');
            }
        }
    </script>
</body>
</html>

