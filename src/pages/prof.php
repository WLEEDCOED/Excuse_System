<?php
session_start();
include '../includes/db_connection.php';

// Ø§Ù„ØªØ£ÙƒØ¯ Ù…Ù† ØªØ³Ø¬ÙŠÙ„ Ø¯Ø®ÙˆÙ„ Ø§Ù„Ù…Ø³ØªØ®Ø¯Ù… ÙƒØ¯ÙƒØªÙˆØ±
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != '2') {
    header('Location: login.php');
    exit;
}

$professor_name = $_SESSION['name']; // Ø¬Ù„Ø¨ Ø§Ø³Ù… Ø§Ù„Ø¯ÙƒØªÙˆØ± Ù…Ù† Ø§Ù„Ø¬Ù„Ø³Ø©

// Ø¬Ù„Ø¨ Ø¬Ù…ÙŠØ¹ Ø§Ù„Ø·Ù„Ø¨Ø§Øª Ù„Ù‡Ø°Ø§ Ø§Ù„Ø¯ÙƒØªÙˆØ±ØŒ Ø¨Ù…Ø§ ÙÙŠ Ø°Ù„Ùƒ Ø¨ÙŠØ§Ù†Ø§Øª Ø§Ù„Ø·Ø§Ù„Ø¨
$sql_requests = "SELECT lecture_excuses.id, students.name AS student_name, students.id AS university_id, course.name AS subject_name, 
                        lecture_excuses.description, lecture_excuses.file_path, lecture_excuses.status, lecture_excuses.created_at, 
                        lecture_excuses.section_number, lecture_excuses.absence_date AS absence_date
                 FROM lecture_excuses
                 JOIN students ON lecture_excuses.student_id = students.id
                 JOIN course ON lecture_excuses.lectures_id = course.id
                 WHERE lecture_excuses.professor_id = ? AND lecture_excuses.status = 'pending'";

$stmt = $conn->prepare($sql_requests);
if ($stmt === false) {
    die("Error in SQL: " . $conn->error);  // Ø·Ø¨Ø§Ø¹Ø© Ø®Ø·Ø£ Ù‚Ø§Ø¹Ø¯Ø© Ø§Ù„Ø¨ÙŠØ§Ù†Ø§Øª
}
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result_requests = $stmt->get_result();

// Ù…Ø¹Ø§Ù„Ø¬Ø© Ù‚Ø¨ÙˆÙ„ Ø£Ùˆ Ø±ÙØ¶ Ø§Ù„Ø·Ù„Ø¨Ø§Øª
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['request_id'])) {
    $action = $_POST['action'];
    $request_id = $_POST['request_id'];
    
    $status = ($action === 'approved') ? 'approved' : 'rejected';

    // ØªØ­Ø¯ÙŠØ« Ø­Ø§Ù„Ø© Ø§Ù„Ø·Ù„Ø¨ ÙÙŠ Ù‚Ø§Ø¹Ø¯Ø© Ø§Ù„Ø¨ÙŠØ§Ù†Ø§Øª
    $sql_update = "UPDATE lecture_excuses SET status = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("si", $status, $request_id);

    if ($stmt_update->execute()) {
        // Ø¥Ø±Ø³Ø§Ù„ Ø±Ø¯ Ù†Ø§Ø¬Ø­ Ù…Ø¹ Ø§Ù„Ø­Ø§Ù„Ø©
        echo json_encode(['success' => true, 'message' => 'ØªÙ… ØªØ­Ø¯ÙŠØ« Ø§Ù„Ø·Ù„Ø¨ Ø¨Ù†Ø¬Ø§Ø­.', 'status' => $status]);
    } else {
        // ÙÙŠ Ø­Ø§Ù„ Ø­Ø¯ÙˆØ« Ø®Ø·Ø£
        echo json_encode(['success' => false, 'message' => 'Ø­Ø¯Ø« Ø®Ø·Ø£ Ø£Ø«Ù†Ø§Ø¡ ØªØ­Ø¯ÙŠØ« Ø§Ù„Ø·Ù„Ø¨.']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>Ø§Ù„Ø·Ù„Ø¨Ø§Øª Ø§Ù„Ù…Ù‚Ø¯Ù…Ø©</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* Ø¥Ø¹Ø¯Ø§Ø¯Ø§Øª Ø¹Ø§Ù…Ø© */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
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
        .header {
            background-color: rgba(42, 157, 143, 0.9);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0px 4px 12px rgba(0,0,0,0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .header .logo {
            font-size: 24px;
            font-weight: bold;
            color: white;
            text-decoration: none;
        }

        .header .professor-name {
            font-size: 20px;
            font-weight: 700;
        }

        .header .nav a.logout-btn {
            background-color: #e76f51;
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.3s ease;
            text-decoration: none;
            font-size: 16px;
        }

        .header .nav a.logout-btn:hover {
            background-color: #d1495b;
            transform: translateY(-2px);
        }

        /* Ø¹Ù†ÙˆØ§Ù† Ø§Ù„ØµÙØ­Ø© */
        h2 { 
            text-align: center; 
            color: #2a9d8f; 
            margin: 100px 0 30px; /* Ø²ÙŠØ§Ø¯Ø© Ø§Ù„Ù…Ø³Ø§ÙØ© Ø§Ù„Ø¹Ù„ÙˆÙŠØ© Ù„Ù…Ù†Ø¹ Ø§Ù„Ø§Ø®ØªØ¨Ø§Ø¡ Ø®Ù„Ù Ø§Ù„Ù‡ÙŠØ¯Ø± */
            font-size: 32px;
        }

        /* Ø­Ø§ÙˆÙŠØ© Ø§Ù„Ø·Ù„Ø¨Ø§Øª */
        .container {
            max-width: 900px;
            margin: 0 auto 40px;
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.95); /* ØªØ®ÙÙŠÙ Ø§Ù„Ø®Ù„ÙÙŠØ© Ù„Ù„Ø±Ø§Ø­Ø© Ø§Ù„Ø¨ØµØ±ÙŠØ© */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            display: flex;
            flex-direction: column; /* Ù„ØªØ¸Ù‡Ø± Ø§Ù„Ø·Ù„Ø¨Ø§Øª ØªØ­Øª Ø¨Ø¹Ø¶Ù‡Ø§ */
            gap: 20px; /* Ù…Ø³Ø§ÙØ© Ø¨ÙŠÙ† ÙƒÙ„ Ø·Ù„Ø¨ ÙˆØ¢Ø®Ø± */
            margin-top: 80px; /* Ù„Ø¶Ù…Ø§Ù† Ø¹Ø¯Ù… Ø§Ø®ØªÙØ§Ø¡ Ø§Ù„Ù…Ø­ØªÙˆÙ‰ Ø®Ù„Ù Ø§Ù„Ù‡ÙŠØ¯Ø± */
        }

        /* ØªÙ†Ø³ÙŠÙ‚ Ø§Ù„Ø·Ù„Ø¨Ø§Øª */
        .request {
            background-color: #ffffff; /* Ø®Ù„ÙÙŠØ© Ø¨ÙŠØ¶Ø§Ø¡ */
            border: 2px solid #3aaea1; /* Ø¥Ø·Ø§Ø± Ø¨Ù„ÙˆÙ† Ù…Ù…ÙŠØ² */
            border-radius: 10px;
            padding: 20px;
            color: #333;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column; /* Ø§Ù„ØªØ£ÙƒØ¯ Ù…Ù† Ø£Ù† Ø§Ù„Ù…Ø­ØªÙˆÙŠØ§Øª Ø¯Ø§Ø®Ù„ Ø§Ù„Ø·Ù„Ø¨ Ù…Ù†Ø¸Ù…Ø© Ø¹Ù…ÙˆØ¯ÙŠØ§Ù‹ */
            gap: 10px; /* Ø¥Ø¶Ø§ÙØ© Ù…Ø³Ø§ÙØ© Ø¨ÙŠÙ† Ø§Ù„Ø¹Ù†Ø§ØµØ± Ø¯Ø§Ø®Ù„ Ø§Ù„Ø·Ù„Ø¨ */
            position: relative;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .request:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }

        /* Ø±Ø£Ø³ Ø§Ù„Ø·Ù„Ø¨ Ø¨Ù„ÙˆÙ† Ù…Ù…ÙŠØ² */
        .request-header {
            background-color: #3aaea1; /* Ù„ÙˆÙ† Ø£Ø®Ø¶Ø± Ù…Ù…ÙŠØ² */
            color: #fff;
            padding: 10px;
            border-radius: 8px 8px 0 0;
            font-size: 18px;
            font-weight: bold;
        }

        .request-info {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .request-info h3 {
            font-size: 18px;
            color: #2a9d8f;
            margin-bottom: 5px;
        }

        .request-info p {
            font-size: 14px;
            color: #555;
            line-height: 1.5;
        }

        .request-info a {
            color: #00796b;
            text-decoration: none;
            font-weight: bold;
        }

        .request-info a:hover {
            text-decoration: underline;
        }

        /* ØªÙ†Ø³ÙŠÙ‚ Ø§Ù„Ø£Ø²Ø±Ø§Ø± */
        .actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 10px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.3s ease;
            font-size: 14px;
        }

        .btn-accept {
            background-color: #28a745;
            color: white;
        }

        .btn-reject {
            background-color: #dc3545;
            color: white;
        }

        .btn-accept:hover {
            background-color: #218838;
        }

        .btn-reject:hover {
            background-color: #c82333;
        }

        /* Ø±Ø³Ø§Ù„Ø© Ø¹Ø¯Ù… ÙˆØ¬ÙˆØ¯ Ø·Ù„Ø¨Ø§Øª */
        .empty-message {
            text-align: center;
            padding: 50px;
            font-size: 20px;
            color: #888;
        }

        /* Ø§Ù„ÙÙˆØªØ± */
        footer {
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            text-align: center;
            padding: 20px 40px;
            width: 100%;
            box-shadow: 0px -4px 12px rgba(0,0,0,0.1);
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
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .header .nav a.logout-btn {
                margin-top: 10px;
                width: 100%;
                text-align: center;
            }

            h2 {
                font-size: 28px;
                margin: 120px 0 20px;
            }

            .container {
                padding: 15px;
                margin: 80px auto 20px;
            }

            .request {
                padding: 15px;
            }

            .request-header {
                font-size: 16px;
            }

            .request-info h3 {
                font-size: 16px;
            }

            .request-info p {
                font-size: 13px;
            }

            .actions button {
                padding: 8px 16px;
                font-size: 14px;
            }

            /* ØªØ­Ø³ÙŠÙ† Ø­Ø¬Ù… Ø§Ù„Ù†ØµÙˆØµ Ù„Ù„Ø­Ø§Ù„Ø§Øª */
            .status {
                font-size: 12px;
                padding: 4px 8px;
            }
        }

        @media (max-width: 480px) {
            h2 {
                font-size: 24px;
            }

            .request-info h3 {
                font-size: 16px;
            }

            .request-info p {
                font-size: 13px;
            }

            .actions button {
                padding: 6px 12px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>

    <!-- Ø§Ù„Ù‡ÙŠØ¯Ø± -->
    <div class="header">

        <div class="professor-name">Ù…Ø±Ø­Ø¨Ø§Ù‹ØŒ Ø¯ÙƒØªÙˆØ± <?php echo htmlspecialchars($professor_name); ?></div>
        <nav class="nav">
            <a href="../php/logout.php" class="logout-btn">ØªØ³Ø¬ÙŠÙ„ Ø§Ù„Ø®Ø±ÙˆØ¬</a>
        </nav>
    </div>

    <!-- Ø¹Ù†ÙˆØ§Ù† Ø§Ù„ØµÙØ­Ø© -->
    <h2>Ø§Ù„Ø·Ù„Ø¨Ø§Øª Ø§Ù„Ù…Ù‚Ø¯Ù…Ø©</h2>

    <!-- Ø­Ø§ÙˆÙŠØ© Ø§Ù„Ø·Ù„Ø¨Ø§Øª -->
    <div class="container">
        <?php
        if ($result_requests->num_rows > 0) {
            while ($row = $result_requests->fetch_assoc()) {
                echo "<div class='request' id='row-{$row['id']}'>";
                echo "<div class='request-header'>Ø·Ù„Ø¨ Ø§Ù„ØºÙŠØ§Ø¨</div>";
                echo "<div class='request-info'>";
                echo "<h3>Ø§Ø³Ù… Ø§Ù„Ø·Ø§Ù„Ø¨: " . htmlspecialchars($row['student_name']) . " (Ø§Ù„Ø±Ù‚Ù… Ø§Ù„Ø¬Ø§Ù…Ø¹ÙŠ: " . htmlspecialchars($row['university_id']) . ")</h3>";
                echo "<h3>Ø§Ù„Ù…Ø§Ø¯Ø©: " . htmlspecialchars($row['subject_name']) . "</h3>";
                echo "<p>Ø§Ù„ÙˆØµÙ: " . htmlspecialchars($row['description']) . "</p>";
                echo "<p><a href='../php/view_file.php?file=" . urlencode($row['file_path']) . "' target='_blank'>عرض الملف</a></p>";
                echo "<p>ØªØ§Ø±ÙŠØ® Ø§Ù„ØºÙŠØ§Ø¨: " . htmlspecialchars($row['absence_date']) . "</p>";
                echo "<p>Ø±Ù‚Ù… Ø§Ù„Ø´Ø¹Ø¨Ø©: " . htmlspecialchars($row['section_number']) . "</p>";
                echo "</div>";
                echo "<div class='actions'>";
                echo "<button class='btn btn-accept' data-id='" . $row['id'] . "'>Ù‚Ø¨ÙˆÙ„</button>";
                echo "<button class='btn btn-reject' data-id='" . $row['id'] . "'>Ø±ÙØ¶</button>";
                echo "</div>";
                echo "</div>";
            }
        } else {
            echo "<div class='empty-message'>Ù„Ø§ ØªÙˆØ¬Ø¯ Ø·Ù„Ø¨Ø§Øª Ø¨Ø¹Ø¯.</div>";
        }
        ?>
    </div>

    <!-- Ø§Ù„ØªØ°ÙŠÙŠÙ„ -->
    <footer>
        <p>&copy; 2024 Ø¨ÙˆØ§Ø¨Ø© ØªØ³ØªÙ‡ÙŠÙ„. Ø¬Ù…ÙŠØ¹ Ø§Ù„Ø­Ù‚ÙˆÙ‚ Ù…Ø­ÙÙˆØ¸Ø©.</p>
       
    </footer>

    <!-- Ù…ÙƒØªØ¨Ø© jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <!-- Ø¬Ø§ÙØ§Ø³ÙƒØ±ÙŠØ¨Øª Ù„Ù…Ø¹Ø§Ù„Ø¬Ø© Ù‚Ø¨ÙˆÙ„ Ø£Ùˆ Ø±ÙØ¶ Ø§Ù„Ø·Ù„Ø¨Ø§Øª -->
    <script>
    $(document).ready(function() {
        $('.btn-accept, .btn-reject').click(function() {
            var button = $(this);
            var requestId = button.data('id');
            var action = button.hasClass('btn-accept') ? 'approved' : 'rejected';
            var row = button.closest('.request');
            
            button.prop('disabled', true);

            $.ajax({
                url: '<?php echo $_SERVER['PHP_SELF']; ?>',
                type: 'POST',
                data: {
                    action: action,
                    request_id: requestId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        var statusMessage = (response.status === 'approved') ? 'ØªÙ…Øª Ø§Ù„Ù…ÙˆØ§ÙÙ‚Ø©' : 'ØªÙ… Ø§Ù„Ø±ÙØ¶';
                        var statusClass = (response.status === 'approved') ? 'approved' : 'rejected';
                        
                        // Ø¹Ø±Ø¶ Ø­Ø§Ù„Ø© Ø§Ù„Ø·Ù„Ø¨ ÙˆØ§Ø³ØªØ¨Ø¯Ø§Ù„ Ø§Ù„Ø£Ø²Ø±Ø§Ø±
                        row.find('.actions').html('<p class="status ' + statusClass + '">' + statusMessage + '</p>');

                        // Ø§Ù„Ø§Ù†ØªØ¸Ø§Ø± Ù„Ø«ÙˆØ§Ù†ÙŠ Ù‚Ù„ÙŠÙ„Ø© Ù‚Ø¨Ù„ Ø¥Ø®ÙØ§Ø¡ Ø§Ù„Ø·Ù„Ø¨
                        setTimeout(function() {
                            row.fadeOut(500, function() {
                                $(this).remove();
                                if ($('.request').length === 0) {
                                    $('.container').append('<div class="empty-message">Ù„Ø§ ØªÙˆØ¬Ø¯ Ø·Ù„Ø¨Ø§Øª Ø¨Ø¹Ø¯.</div>');
                                }
                            });
                        }, 2000); // Ø§Ù†ØªØ¸Ø± 2 Ø«Ø§Ù†ÙŠØ© Ù‚Ø¨Ù„ Ø§Ù„Ø¥Ø®ÙØ§Ø¡
                    } else {
                        alert(response.message);
                        button.prop('disabled', false);
                    }
                },
                error: function() {
                    alert('Ø­Ø¯Ø« Ø®Ø·Ø£ Ø£Ø«Ù†Ø§Ø¡ Ø§Ù„Ø§ØªØµØ§Ù„ Ø¨Ø§Ù„Ø®Ø§Ø¯Ù…');
                    button.prop('disabled', false);
                }
            });
        });
    });
    </script>
</body>
</html>

