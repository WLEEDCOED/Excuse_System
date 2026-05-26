<?php
session_start();
include '../includes/db_connection.php'; // ØªØ£ÙƒØ¯ Ù…Ù† Ø£Ù† Ù…Ø³Ø§Ø± Ù…Ù„Ù Ø§Ù„Ø§ØªØµØ§Ù„ Ø¨Ù‚Ø§Ø¹Ø¯Ø© Ø§Ù„Ø¨ÙŠØ§Ù†Ø§Øª ØµØ­ÙŠØ­

// Ø§Ù„ØªØ£ÙƒØ¯ Ù…Ù† ØªØ³Ø¬ÙŠÙ„ Ø§Ù„Ø¯Ø®ÙˆÙ„ ÙƒØ·Ø§Ù„Ø¨
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != '3') {
    header('Location: login.php');
    exit;
}

$student_id = $_SESSION['user_id'];  // Ù‡Ø°Ø§ ÙŠØ¶Ù…Ù† Ø§Ø³ØªØ®Ø¯Ø§Ù… Ù…Ø¹Ø±Ù Ø§Ù„Ø·Ø§Ù„Ø¨ Ø§Ù„Ù…Ø³Ø¬Ù„

// Ø¬Ù„Ø¨ Ø§Ù„Ù…ÙˆØ§Ø¯ Ù…Ù† Ø¬Ø¯ÙˆÙ„ `classes`
$sql_Lectures = "SELECT * FROM Course";
$result_subjects = $conn->query($sql_Lectures);

// Ø¬Ù„Ø¨ Ø§Ù„Ø£Ø³Ø§ØªØ°Ø© Ù…Ù† Ø¬Ø¯ÙˆÙ„ `professors`
$sql_professors = "SELECT * FROM professors";
$result_professors = $conn->query($sql_professors);

// Ø§Ù„ØªØ­Ù‚Ù‚ Ù…Ù† Ø¥Ø±Ø³Ø§Ù„ Ø§Ù„Ù†Ù…ÙˆØ°Ø¬
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $response = [];

    // Ø§Ø³ØªØ±Ø¬Ø§Ø¹ Ø¨ÙŠØ§Ù†Ø§Øª Ø§Ù„Ø·Ù„Ø¨
    $lectures_id = $_POST['subject_id']; 
    $professor_id = $_POST['professor_id']; 
    $description = $_POST['description'];
    $section_number = $_POST['section_number'];  // Ø±Ù‚Ù… Ø§Ù„Ø´Ø¹Ø¨Ø©
    $absence_date = $_POST['absence_date'];  // ØªØ§Ø±ÙŠØ® Ø§Ù„ØºÙŠØ§Ø¨

    // Ù…Ø¹Ø§Ù„Ø¬Ø© Ø±ÙØ¹ Ø§Ù„Ù…Ù„Ù
    $target_dir = __DIR__ . "/../../uploads/";
    $file_name = basename($_FILES["excuse_file"]["name"]);
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    // Ø§Ù„ØªØ­Ù‚Ù‚ Ù…Ù† Ø­Ø¬Ù… Ø§Ù„Ù…Ù„Ù (Ù…Ø­Ø¯Ø¯ Ø¨Ù€ 5MB)
    if ($_FILES["excuse_file"]["size"] > 5000000) {
        $response['error'] = "Ø¹Ø°Ø±Ø§Ù‹ØŒ Ø­Ø¬Ù… Ø§Ù„Ù…Ù„Ù ÙƒØ¨ÙŠØ± Ø¬Ø¯Ø§Ù‹.";
        $uploadOk = 0;
    }

    // Ø§Ù„Ø³Ù…Ø§Ø­ ÙÙ‚Ø· Ø¨Ø£Ù†ÙˆØ§Ø¹ Ù…Ø¹ÙŠÙ†Ø© Ù…Ù† Ø§Ù„Ù…Ù„ÙØ§Øª
    $allowed_types = array("pdf", "doc", "docx");
    if (!in_array($fileType, $allowed_types)) {
        $response['error'] = "Ø¹Ø°Ø±Ø§Ù‹ØŒ ÙÙ‚Ø· Ø§Ù„Ù…Ù„ÙØ§Øª Ù…Ù† Ù†ÙˆØ¹ PDF, DOC, DOCX Ù…Ø³Ù…ÙˆØ­ Ø¨Ù‡Ø§.";
        $uploadOk = 0;
    }

    if ($uploadOk == 0) {
        if (!isset($response['error'])) {
            $response['error'] = "Ø¹Ø°Ø±Ø§Ù‹ØŒ Ù„Ù… ÙŠØªÙ… Ø±ÙØ¹ Ø§Ù„Ù…Ù„Ù.";
        }
    } else {
        // Ø¥Ù†Ø´Ø§Ø¡ Ø§Ø³Ù… Ù…Ù„Ù ÙØ±ÙŠØ¯
        $new_file_name = uniqid('file_', true) . '.' . $fileType;
        $target_file = $target_dir . $new_file_name;
        $stored_file_path = 'uploads/' . $new_file_name;

        if (move_uploaded_file($_FILES["excuse_file"]["tmp_name"], $target_file)) {
            // Ø¬Ù„Ø¨ Ù‚Ø³Ù… Ø§Ù„Ø·Ø§Ù„Ø¨
            $sql_student = "SELECT department_id FROM students WHERE id = ?";
            $stmt_student = $conn->prepare($sql_student);
            $stmt_student->bind_param("i", $student_id);
            $stmt_student->execute();
            $result_student = $stmt_student->get_result();

            if ($result_student->num_rows > 0) {
                $student = $result_student->fetch_assoc();
                $student_department_id = $student['department_id'];
            } else {
                $response['error'] = "ØªØ¹Ø°Ø± Ø§Ù„Ø¹Ø«ÙˆØ± Ø¹Ù„Ù‰ Ø¨ÙŠØ§Ù†Ø§Øª Ø§Ù„Ø·Ø§Ù„Ø¨.";
                echo json_encode($response);
                exit;
            }

       
            // Ø¥Ø¯Ø®Ø§Ù„ Ø¨ÙŠØ§Ù†Ø§Øª Ø§Ù„Ø·Ù„Ø¨ ÙÙŠ Ù‚Ø§Ø¹Ø¯Ø© Ø§Ù„Ø¨ÙŠØ§Ù†Ø§ØªØŒ Ø¨Ù…Ø§ ÙÙŠ Ø°Ù„Ùƒ created_at
            $sql = "INSERT INTO lecture_excuses (lectures_id, professor_id, student_id, description, file_path, status, absence_date, section_number) 
                    VALUES (?, ?, ?, ?, ?, 'Pending', ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiissss", $lectures_id, $professor_id, $student_id, $description, $stored_file_path, $absence_date, $section_number);

            if ($stmt->execute()) {
                $response['success'] = "ØªÙ… ØªÙ‚Ø¯ÙŠÙ… Ø§Ù„Ø¹Ø°Ø± Ø¨Ù†Ø¬Ø§Ø­ ÙˆØ³ÙŠØªÙ… Ù…Ø±Ø§Ø¬Ø¹ØªÙ‡.";
            } else {
                $response['error'] = "Ø®Ø·Ø£: " . $stmt->error;
            }
        } else {
            $response['error'] = "Ø¹Ø°Ø±Ø§Ù‹ØŒ Ø­Ø¯Ø« Ø®Ø·Ø£ Ø£Ø«Ù†Ø§Ø¡ Ø±ÙØ¹ Ø§Ù„Ù…Ù„Ù.";
        }
    }

    echo json_encode($response);
    exit;
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>Ø·Ù„Ø¨ Ø¹Ø°Ø± - Ø¨ÙˆØ§Ø¨Ø© Ø§Ù„Ø·Ù„Ø§Ø¨</title>
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
            background-color: #eceff1;
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
        .content-wrapper {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding-top: 80px; /* Ù„Ù…Ù†Ø¹ Ø§Ù„Ù…Ø­ØªÙˆÙ‰ Ù…Ù† Ø§Ù„Ø§Ø®ØªØ¨Ø§Ø¡ Ø®Ù„Ù Ø§Ù„Ù‡ÙŠØ¯Ø± Ø§Ù„Ø«Ø§Ø¨Øª */
            padding-bottom: 20px;
        }

        .form-container {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 15px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
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
        .form-container .student-icon {
            width: 80px;
            margin-bottom: 10px;
        }

        /* Ø§Ù„Ø¹Ù†ÙˆØ§Ù† */
        .form-container h2 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
            text-align: center;
        }

        /* Ø­Ù‚ÙˆÙ„ Ø§Ù„Ø¥Ø¯Ø®Ø§Ù„ */
        .form-container select,
        .form-container input[type="date"],
        .form-container input[type="text"],
        .form-container textarea,
        .form-container input[type="file"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            background-color: #fafafa;
            transition: border-color 0.3s ease, background-color 0.3s ease;
            box-sizing: border-box;
        }

        .form-container select:hover,
        .form-container input[type="date"]:hover,
        .form-container input[type="text"]:hover,
        .form-container textarea:hover,
        .form-container input[type="file"]:hover,
        .form-container select:focus,
        .form-container input[type="date"]:focus,
        .form-container input[type="text"]:focus,
        .form-container textarea:focus,
        .form-container input[type="file"]:focus {
            border-color: #4CAF50;
            background-color: #f1f8ff;
            outline: none;
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        /* Ø²Ø± Ø§Ù„Ø¥Ø±Ø³Ø§Ù„ */
        .form-container button {
            width: 100%;
            padding: 15px;
            background-color: #2a9d8f;
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease;
            margin-top: 20px;
        }

        .form-container button:hover {
            background-color: #21867a;
            transform: translateY(-2px);
        }

        /* Ø±Ø³Ø§Ù„Ø© Ø§Ù„Ù†Ø¬Ø§Ø­ ÙˆØ§Ù„Ø®Ø·Ø£ */
        .success-message, .error-message {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
            font-size: 16px;
        }

        .success-message {
            background-color: #4caf50;
            color: #ffffff;
        }

        .error-message {
            background-color: #f44336;
            color: #ffffff;
        }

        /* Ø§Ù„ÙÙˆØªØ± */
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

            .form-container {
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
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        $(document).ready(function(){
            $('#requestForm').on('submit', function(event){
                event.preventDefault();
                var formData = new FormData(this);
                $.ajax({
                    url: '',
                    method: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        var res = JSON.parse(response);
                        if(res.success) {
                            $('#message').html('<div class="success-message">'+res.success+'</div>');
                            $('#requestForm')[0].reset();
                        } else if(res.error) {
                            $('#message').html('<div class="error-message">'+res.error+'</div>');
                        }
                    }
                });
            });
        });
    </script>
</head>
<body>

    <!-- Ø§Ù„Ù‡ÙŠØ¯Ø± (Header) -->
    <nav class="navbar">
        <div class="nav-logo">Ø¨ÙˆØ§Ø¨Ø© Ø§Ù„Ø·Ù„Ø§Ø¨</div>
        <button class="logout-btn" onclick="window.location.href='../php/logout.php'">ØªØ³Ø¬ÙŠÙ„ Ø§Ù„Ø®Ø±ÙˆØ¬</button>
    </nav>

    <!-- Ù…Ø­ØªÙˆÙ‰ Ø§Ù„ØµÙØ­Ø© -->
    <div class="content-wrapper">
        <div class="form-container">
            <!-- Ø£ÙŠÙ‚ÙˆÙ†Ø© Ø§Ù„Ø¥ØºÙ„Ø§Ù‚ -->
            <a href="Home.php">
                <img src="../assets/images/letter-x.png" alt="Ø¥ØºÙ„Ø§Ù‚" class="close-icon"> <!-- ØªØ£ÙƒØ¯ Ù…Ù† Ø£Ù† Ù…Ø³Ø§Ø± Ø§Ù„ØµÙˆØ±Ø© ØµØ­ÙŠØ­ -->
            </a>

            <!-- Ø£ÙŠÙ‚ÙˆÙ†Ø© Ø§Ù„Ø·Ø§Ù„Ø¨ -->
            <img src="../assets/images/male-student.png" alt="Ø£ÙŠÙ‚ÙˆÙ†Ø© Ø§Ù„Ø·Ø§Ù„Ø¨" class="student-icon"> <!-- ØªØ£ÙƒØ¯ Ù…Ù† Ø£Ù† Ù…Ø³Ø§Ø± Ø§Ù„ØµÙˆØ±Ø© ØµØ­ÙŠØ­ -->

            <h2>Ø¹Ø°Ø± Ø§Ù„ØºÙŠØ§Ø¨ Ø¹Ù† Ø§Ù„Ù…Ø­Ø§Ø¶Ø±Ø§Øª</h2>

            <!-- Ø¹Ø±Ø¶ Ø±Ø³Ø§Ù„Ø© Ø§Ù„Ù†Ø¬Ø§Ø­ Ø£Ùˆ Ø§Ù„Ø®Ø·Ø£ -->
            <div id="message">
                <?php 
                if (isset($response)) {
                    if (isset($response['success'])) {
                        echo "<div class='success-message'>{$response['success']}</div>";
                    } elseif (isset($response['error'])) {
                        echo "<div class='error-message'>{$response['error']}</div>";
                    }
                }
                ?>
            </div>

            <!-- Ù†Ù…ÙˆØ°Ø¬ Ø¥Ø±Ø³Ø§Ù„ Ø§Ù„Ø·Ù„Ø¨ -->
            <form id="requestForm" method="post" enctype="multipart/form-data">
                <label for="subject">Ø§Ø®ØªØ± Ø§Ù„Ù…Ø§Ø¯Ø©:</label>
                <select name="subject_id" id="subject" required>
                    <option value="">Ø§Ø®ØªØ± Ø§Ù„Ù…Ø§Ø¯Ø©</option>
                    <?php
                    // Ø¹Ø±Ø¶ Ø§Ù„Ù…ÙˆØ§Ø¯ ÙÙŠ Ø§Ù„Ù‚Ø§Ø¦Ù…Ø© Ø§Ù„Ù…Ù†Ø³Ø¯Ù„Ø©
                    if ($result_subjects->num_rows > 0) {
                        while ($row = $result_subjects->fetch_assoc()) {
                            echo "<option value='" . htmlspecialchars($row['id']) . "'>" . htmlspecialchars($row['name']) . "</option>";
                        }
                    }
                    ?>
                </select>

                <label for="professor">Ø§Ø®ØªØ± Ø§Ù„Ø¯ÙƒØªÙˆØ±:</label>
                <select name="professor_id" id="professor" required>
                    <option value="">Ø§Ø®ØªØ± Ø§Ù„Ø¯ÙƒØªÙˆØ±</option>
                    <?php
                    // Ø¹Ø±Ø¶ Ø§Ù„Ø£Ø³Ø§ØªØ°Ø© ÙÙŠ Ø§Ù„Ù‚Ø§Ø¦Ù…Ø© Ø§Ù„Ù…Ù†Ø³Ø¯Ù„Ø©
                    if ($result_professors->num_rows > 0) {
                        while ($row = $result_professors->fetch_assoc()) {
                            echo "<option value='" . htmlspecialchars($row['id']) . "'>" . htmlspecialchars($row['name']) . "</option>";
                        }
                    }
                    ?>
                </select>

                <label for="description">ÙˆØµÙ Ø§Ù„Ø¹Ø°Ø±:</label>
                <textarea name="description" id="description" required></textarea>

                <label for="excuse_file">Ø£Ø±ÙÙ‚ Ø§Ù„Ø¹Ø°Ø± (Ù…Ù„Ù):</label>
                <input type="file" name="excuse_file" id="excuse_file" required>

                <label for="section_number">Ø±Ù‚Ù… Ø§Ù„Ø´Ø¹Ø¨Ø©:</label>
                <input type="text" name="section_number" id="section_number" required>

                <label for="absence_date">ØªØ§Ø±ÙŠØ® Ø§Ù„ØºÙŠØ§Ø¨:</label>
                <input type="date" name="absence_date" id="absence_date" required>

                <button type="submit">Ø¥Ø±Ø³Ø§Ù„ Ø§Ù„Ø¹Ø°Ø±</button>
            </form>
        </div>
    </div>

    <!-- Ø§Ù„ÙÙˆØªØ± (Footer) -->
    <footer>
  
        <p>&copy; 2024 Ø¨ÙˆØ§Ø¨Ø© ØªØ³Ù‡ÙŠÙ„. Ø¬Ù…ÙŠØ¹ Ø§Ù„Ø­Ù‚ÙˆÙ‚ Ù…Ø­ÙÙˆØ¸Ø©.</p>
    </footer>

</body>
</html>


