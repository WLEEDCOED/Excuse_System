<?php
// ØªÙ…ÙƒÙŠÙ† Ø¹Ø±Ø¶ Ø§Ù„Ø£Ø®Ø·Ø§Ø¡ Ø£Ø«Ù†Ø§Ø¡ Ø§Ù„ØªØ·ÙˆÙŠØ±
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include '../includes/db_connection.php'; // ØªØ£ÙƒØ¯ Ù…Ù† ØªØ¶Ù…ÙŠÙ† Ù…Ù„Ù Ø§Ù„Ø§ØªØµØ§Ù„ Ø¨Ù‚Ø§Ø¹Ø¯Ø© Ø§Ù„Ø¨ÙŠØ§Ù†Ø§Øª

// Ø§Ù„ØªØ­Ù‚Ù‚ Ù…Ù† ØªØ³Ø¬ÙŠÙ„ Ø§Ù„Ø¯Ø®ÙˆÙ„
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ø¬Ù„Ø¨ Ù…Ø¹Ø±Ù Ø§Ù„Ù…Ø³ØªØ®Ø¯Ù… Ø§Ù„Ø­Ø§Ù„ÙŠ
$current_admin_id = $_SESSION['user_id'];

// ØªØ­Ø¯ÙŠØ¯ Ù…Ø§ Ø¥Ø°Ø§ ÙƒØ§Ù† Ø§Ù„Ù…Ø³Ø¤ÙˆÙ„ Ø§Ù„Ø­Ø§Ù„ÙŠ ÙÙŠ Ø¬Ø¯ÙˆÙ„ 'admins' Ø£Ùˆ 'users'
if ($_SESSION['table'] == 'admins') {
    $current_admin_table = 'admins';
    $sql_admin = "SELECT name FROM admins WHERE id = ?";
    $stmt_admin = $conn->prepare($sql_admin);
    $stmt_admin->bind_param("i", $current_admin_id);
    $stmt_admin->execute();
    $result_admin = $stmt_admin->get_result();

    if ($result_admin && $result_admin->num_rows > 0) {
        $admin_row = $result_admin->fetch_assoc();
        $current_admin_name = $admin_row['name'];
    } else {
        echo "Ù„Ù… ÙŠØªÙ… Ø§Ù„Ø¹Ø«ÙˆØ± Ø¹Ù„Ù‰ Ø§Ù„Ù…Ø³Ø¤ÙˆÙ„ Ø§Ù„Ø­Ø§Ù„ÙŠ.";
        exit();
    }
} else {
    $current_admin_table = 'users';
    $sql_user = "SELECT name, department_id FROM users WHERE id = ?";
    $stmt_user = $conn->prepare($sql_user);
    $stmt_user->bind_param("i", $current_admin_id);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();

    if ($result_user && $result_user->num_rows > 0) {
        $admin_row = $result_user->fetch_assoc();
        $current_admin_name = $admin_row['name'];
        $current_admin_department_id = $admin_row['department_id'];
    } else {
        echo "Ù„Ù… ÙŠØªÙ… Ø§Ù„Ø¹Ø«ÙˆØ± Ø¹Ù„Ù‰ Ø§Ù„Ù…Ø³Ø¤ÙˆÙ„ Ø§Ù„Ø­Ø§Ù„ÙŠ.";
        exit();
    }
}

// Ù…Ø¹Ø§Ù„Ø¬Ø© Ø·Ù„Ø¨Ø§Øª POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $request_id = $_POST['request_id'] ?? '';

    if ($action == 'approve') {
        if ($current_admin_table == 'users') {
            // Ø§Ù„Ù…Ø³Ø¤ÙˆÙ„ Ø§Ù„Ø£ÙˆÙ„ØŒ Ù†Ù†ØªÙ‚Ù„ Ø¥Ù„Ù‰ Ø§Ù„Ù…Ø³Ø¤ÙˆÙ„ Ø§Ù„Ø«Ø§Ù†ÙŠ ÙÙŠ Ø¬Ø¯ÙˆÙ„ 'admins'
            $sql_next_admin = "SELECT id FROM admins ORDER BY id ASC LIMIT 1";
            $result_next_admin = $conn->query($sql_next_admin);
            if ($result_next_admin && $result_next_admin->num_rows > 0) {
                $next_admin = $result_next_admin->fetch_assoc();
                $next_admin_id = $next_admin['id'];

                // ØªØ­Ø¯ÙŠØ« Ø§Ù„Ø·Ù„Ø¨ Ù„ÙŠØ±Ø³Ù„Ù‡ Ù„Ù„Ù…Ø³Ø¤ÙˆÙ„ Ø§Ù„ØªØ§Ù„ÙŠ
                $sql = "UPDATE final_exam SET current_admin = ?, current_admin_table = 'admins', current_level = current_level + 1 WHERE id = ?";
                $stmt_update = $conn->prepare($sql);
                $stmt_update->bind_param("ii", $next_admin_id, $request_id);
                if ($stmt_update->execute()) {
                    echo json_encode(['showDatePicker' => false]);
                } else {
                    error_log("Error updating to next admin: " . $stmt_update->error);
                    echo json_encode(['error' => 'Ø­Ø¯Ø« Ø®Ø·Ø£ Ø£Ø«Ù†Ø§Ø¡ ØªØ­Ø¯ÙŠØ« Ø§Ù„Ø·Ù„Ø¨.']);
                }
            } else {
                // Ø¥Ø°Ø§ Ù„Ù… ÙŠÙƒÙ† Ù‡Ù†Ø§Ùƒ Ù…Ø³Ø¤ÙˆÙ„ÙˆÙ† ÙÙŠ Ø¬Ø¯ÙˆÙ„ 'admins'
                $sql = "UPDATE final_exam SET status = 'Approved', processed_by = ? WHERE id = ?";
                $stmt_update = $conn->prepare($sql);
                $stmt_update->bind_param("ii", $current_admin_id, $request_id);
                if ($stmt_update->execute()) {
                    echo json_encode(['showDatePicker' => true, 'requestId' => $request_id]);
                } else {
                    error_log("Error approving request: " . $stmt_update->error);
                    echo json_encode(['error' => 'Ø­Ø¯Ø« Ø®Ø·Ø£ Ø£Ø«Ù†Ø§Ø¡ ØªØ­Ø¯ÙŠØ« Ø§Ù„Ø·Ù„Ø¨.']);
                }
            }
        } elseif ($current_admin_table == 'admins') {
            // Ø§Ù„Ù…Ø³Ø¤ÙˆÙ„ Ø§Ù„Ø­Ø§Ù„ÙŠ ÙÙŠ Ø¬Ø¯ÙˆÙ„ 'admins'
            // Ø¬Ù„Ø¨ Ø¬Ù…ÙŠØ¹ Ø§Ù„Ù…Ø³Ø¤ÙˆÙ„ÙŠÙ† Ù…Ù† Ø¬Ø¯ÙˆÙ„ 'admins'
            $sql_admins = "SELECT id FROM admins ORDER BY id ASC";
            $admins_result = $conn->query($sql_admins);
            if (!$admins_result) {
                die("Error executing query: " . $conn->error);
            }

            $admin_ids = [];
            while ($admin = $admins_result->fetch_assoc()) {
                $admin_ids[] = $admin['id'];
            }

            // Ø§Ù„Ø¨Ø­Ø« Ø¹Ù† Ø§Ù„Ù…Ø³Ø¤ÙˆÙ„ Ø§Ù„Ø­Ø§Ù„ÙŠ ÙÙŠ Ø§Ù„Ù‚Ø§Ø¦Ù…Ø©
            $current_index = array_search($current_admin_id, $admin_ids);

            if ($current_index !== false && $current_index < count($admin_ids) - 1) {
                // ÙŠÙˆØ¬Ø¯ Ù…Ø³Ø¤ÙˆÙ„ ØªØ§Ù„ÙŠ
                $next_admin_id = $admin_ids[$current_index + 1];

                // ØªØ­Ø¯ÙŠØ« Ø§Ù„Ø·Ù„Ø¨ Ù„ÙŠØ±Ø³Ù„Ù‡ Ù„Ù„Ù…Ø³Ø¤ÙˆÙ„ Ø§Ù„ØªØ§Ù„ÙŠ
                $sql = "UPDATE final_exam SET current_admin = ?, current_level = current_level + 1 WHERE id = ?";
                $stmt_update = $conn->prepare($sql);
                $stmt_update->bind_param("ii", $next_admin_id, $request_id);
                if ($stmt_update->execute()) {
                    echo json_encode(['showDatePicker' => false]);
                } else {
                    error_log("Error updating to next admin: " . $stmt_update->error);
                    echo json_encode(['error' => 'Ø­Ø¯Ø« Ø®Ø·Ø£ Ø£Ø«Ù†Ø§Ø¡ ØªØ­Ø¯ÙŠØ« Ø§Ù„Ø·Ù„Ø¨.']);
                }
            } else {
                // Ø¥Ø°Ø§ ÙƒØ§Ù† Ù„Ø§ ÙŠÙˆØ¬Ø¯ Ù…Ø³Ø¤ÙˆÙ„ Ø¢Ø®Ø±ØŒ ÙŠØªÙ… ØªØºÙŠÙŠØ± Ø­Ø§Ù„Ø© Ø§Ù„Ø·Ù„Ø¨ Ø¥Ù„Ù‰ "Approved"
                $sql = "UPDATE final_exam SET status = 'Approved', processed_by = ? WHERE id = ?";
                $stmt_update = $conn->prepare($sql);
                $stmt_update->bind_param("ii", $current_admin_id, $request_id);
                if ($stmt_update->execute()) {
                    echo json_encode(['showDatePicker' => true, 'requestId' => $request_id]);
                } else {
                    error_log("Error approving request: " . $stmt_update->error);
                    echo json_encode(['error' => 'Ø­Ø¯Ø« Ø®Ø·Ø£ Ø£Ø«Ù†Ø§Ø¡ ØªØ­Ø¯ÙŠØ« Ø§Ù„Ø·Ù„Ø¨.']);
                }
            }
        }
        exit();
    } elseif ($action == 'reject') {
        // ØªØ­Ø¯ÙŠØ« Ø­Ø§Ù„Ø© Ø§Ù„Ø·Ù„Ø¨ Ø¥Ù„Ù‰ "Rejected" ÙˆØ¥Ø¶Ø§ÙØ© Ø³Ø¨Ø¨ Ø§Ù„Ø±ÙØ¶
        $rejection_reason = $_POST['rejection_reason'] ?? '';
        if (empty($rejection_reason)) {
            echo json_encode(['error' => 'ÙŠØ±Ø¬Ù‰ Ø¥Ø¯Ø®Ø§Ù„ Ø³Ø¨Ø¨ Ø§Ù„Ø±ÙØ¶.']);
            exit();
        }

        // Ø§Ø³ØªØ®Ø¯Ø§Ù… prepared statements Ø¨Ø´ÙƒÙ„ Ø¢Ù…Ù†
        $sql = "UPDATE final_exam SET status = 'Rejected', rejection_reason = ?, processed_by = ? WHERE id = ?";
        $stmt_reject = $conn->prepare($sql);
        $stmt_reject->bind_param("sii", $rejection_reason, $current_admin_id, $request_id);
        if ($stmt_reject->execute()) {
            echo json_encode(['showDatePicker' => false]);
        } else {
            error_log("Error rejecting request: " . $stmt_reject->error);
            echo json_encode(['error' => 'Ø­Ø¯Ø« Ø®Ø·Ø£ Ø£Ø«Ù†Ø§Ø¡ Ø±ÙØ¶ Ø§Ù„Ø·Ù„Ø¨.']);
        }
        exit();
    } elseif ($action == 'set_exam_date') {
        $exam_date = $_POST['exam_date'] ?? '';

        // Ø§Ù„ØªØ­Ù‚Ù‚ Ù…Ù† Ø£Ù† Ø§Ù„ØªØ§Ø±ÙŠØ® Ù…ÙˆØ¬ÙˆØ¯ ÙˆØµØ­ÙŠØ­
        if (empty($exam_date)) {
            echo json_encode(['error' => 'ØªØ§Ø±ÙŠØ® Ø§Ù„Ø§Ø®ØªØ¨Ø§Ø± ØºÙŠØ± Ù…Ø­Ø¯Ø¯.']);
            exit();
        }

        // Ø§Ù„ØªØ­Ù‚Ù‚ Ù…Ù† ØµØ­Ø© Ø§Ù„ØªØ§Ø±ÙŠØ®
        if (!validateDate($exam_date)) {
            echo json_encode(['error' => 'ØªØ§Ø±ÙŠØ® Ø§Ù„Ø§Ø®ØªØ¨Ø§Ø± ØºÙŠØ± ØµØ§Ù„Ø­.']);
            exit();
        }

        // ØªØ­Ø¯ÙŠØ« Ø§Ù„Ø·Ù„Ø¨ Ø¨ØªØ§Ø±ÙŠØ® Ø§Ù„Ø§Ø®ØªØ¨Ø§Ø±
        $sql_update_exam_date = "UPDATE final_exam SET exam_date = ?, status = 'Approved' WHERE id = ?";
        $stmt_update_exam_date = $conn->prepare($sql_update_exam_date);
        $stmt_update_exam_date->bind_param("si", $exam_date, $request_id);

        if ($stmt_update_exam_date->execute()) {
            echo json_encode(['showDatePicker' => false, 'success' => 'ØªÙ… ØªØ¹ÙŠÙŠÙ† ØªØ§Ø±ÙŠØ® Ø§Ù„Ø§Ø®ØªØ¨Ø§Ø± Ø¨Ù†Ø¬Ø§Ø­.']);
        } else {
            error_log("Error updating exam_date: " . $stmt_update_exam_date->error);
            echo json_encode(['error' => 'Ø­Ø¯Ø« Ø®Ø·Ø£ Ø£Ø«Ù†Ø§Ø¡ ØªØ¹ÙŠÙŠÙ† ØªØ§Ø±ÙŠØ® Ø§Ù„Ø§Ø®ØªØ¨Ø§Ø±.']);
        }
        exit();
    } elseif ($action == 'Under_Progrees') {
        // Ø¥Ø±Ø³Ø§Ù„ Ø§Ù„Ø·Ù„Ø¨ Ø¥Ù„Ù‰ Ù…Ø¬Ù„Ø³ Ø§Ù„ÙƒÙ„ÙŠØ©
        $sql = "UPDATE final_exam SET status = 'Under_Progrees', processed_by = ? WHERE id = ?";
        $stmt_send_council = $conn->prepare($sql);
        $stmt_send_council->bind_param("ii", $current_admin_id, $request_id);
        if ($stmt_send_council->execute()) {
            echo json_encode(['success' => true]);
        } else {
            error_log("Error sending to council: " . $stmt_send_council->error);
            echo json_encode(['success' => false, 'error' => 'Ø­Ø¯Ø« Ø®Ø·Ø£ Ø£Ø«Ù†Ø§Ø¡ Ø¥Ø±Ø³Ø§Ù„ Ø§Ù„Ø·Ù„Ø¨ Ø¥Ù„Ù‰ Ù…Ø¬Ù„Ø³ Ø§Ù„ÙƒÙ„ÙŠØ©.']);
        }
        exit();
    }
}

// Ø§Ø³ØªØ¹Ù„Ø§Ù… ÙŠØ¬ÙŠØ¨ Ø§Ù„Ø·Ù„Ø¨Ø§Øª Ø§Ù„Ù…ÙˆØ¬Ù‡Ø© Ù„Ù„Ù…Ø³Ø¤ÙˆÙ„ Ø§Ù„Ø­Ø§Ù„ÙŠ ÙˆØ§Ù„Ø·Ù„Ø§Ø¨ Ù…Ù† Ù†ÙØ³ Ø§Ù„Ù‚Ø³Ù… (Ù„Ù„Ù…Ø³Ø¤ÙˆÙ„ Ø§Ù„Ø£ÙˆÙ„)
if ($current_admin_table == 'users') {
    $sql_requests = "SELECT final_exam.*, students.name AS student_name, students.department_id AS student_department_id
        FROM final_exam
        JOIN students ON final_exam.student_id = students.id
        WHERE final_exam.status NOT IN ('Approved', 'Rejected')
        AND final_exam.current_admin = ?
        AND final_exam.current_admin_table = ?
        AND students.department_id = ?"; // ØªØµØ­ÙŠØ­ Ø´Ø±Ø· Ø§Ù„Ù‚Ø³Ù… Ù„ÙŠØ³ØªØ®Ø¯Ù… students.department_id

    $stmt_requests = $conn->prepare($sql_requests);
    $stmt_requests->bind_param("isi", $current_admin_id, $current_admin_table, $current_admin_department_id);
} elseif ($current_admin_table == 'admins') {
    // Custom query for admins from the 'admins' table
    $sql_requests = "SELECT final_exam.*, students.name AS student_name
        FROM final_exam
        JOIN students ON final_exam.student_id = students.id
        WHERE final_exam.status NOT IN ('Approved', 'Rejected')
        AND final_exam.current_admin = ?
        AND final_exam.current_admin_table = ?";

    $stmt_requests = $conn->prepare($sql_requests);
    $stmt_requests->bind_param("is", $current_admin_id, $current_admin_table);
}

$stmt_requests->execute();
$result = $stmt_requests->get_result();

if (!$result) {
    die("Error executing query: " . $conn->error);
}

// ÙˆØ¸ÙŠÙØ© Ù„Ù„ØªØ­Ù‚Ù‚ Ù…Ù† ØµØ­Ø© Ø§Ù„ØªØ§Ø±ÙŠØ®
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ù„ÙˆØ­Ø© Ø§Ù„Ù…Ø³Ø¤ÙˆÙ„ - Ø¥Ø¯Ø§Ø±Ø© Ø§Ù„Ø·Ù„Ø¨Ø§Øª</title>
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
            background-color: #f4f7f6;
            background-image: url('../assets/images/BG-login.png');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            direction: rtl;
            color: #333;
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
            max-width: 900px;
            margin: 80px auto 40px; /* Ø²ÙŠØ§Ø¯Ø© Ø§Ù„Ù…Ø³Ø§ÙØ© Ø§Ù„Ø¹Ù„ÙˆÙŠØ© Ù„Ù…Ù†Ø¹ Ø§Ù„Ø§Ø®ØªØ¨Ø§Ø¡ Ø®Ù„Ù Ø§Ù„Ù‡ÙŠØ¯Ø± */
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            display: flex;
            flex-direction: column; /* Ù„ØªØ¸Ù‡Ø± Ø§Ù„Ø·Ù„Ø¨Ø§Øª ØªØ­Øª Ø¨Ø¹Ø¶Ù‡Ø§ */
            gap: 20px; /* Ù…Ø³Ø§ÙØ© Ø¨ÙŠÙ† ÙƒÙ„ Ø·Ù„Ø¨ ÙˆØ¢Ø®Ø± */
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }

        /* ØªÙ†Ø³ÙŠÙ‚ Ø§Ù„Ø·Ù„Ø¨Ø§Øª */
        .request {
            background-color: #2a9d8f;
            border-radius: 10px;
            padding: 20px;
            color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column; /* Ø§Ù„ØªØ£ÙƒØ¯ Ù…Ù† Ø£Ù† Ø§Ù„Ù…Ø­ØªÙˆÙŠØ§Øª Ø¯Ø§Ø®Ù„ Ø§Ù„Ø·Ù„Ø¨ Ù…Ù†Ø¸Ù…Ø© Ø¹Ù…ÙˆØ¯ÙŠØ§Ù‹ */
            gap: 10px; /* Ø¥Ø¶Ø§ÙØ© Ù…Ø³Ø§ÙØ© Ø¨ÙŠÙ† Ø§Ù„Ø¹Ù†Ø§ØµØ± Ø¯Ø§Ø®Ù„ Ø§Ù„Ø·Ù„Ø¨ */
            position: relative;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .request:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .request-info {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px; /* Ø¥Ø¶Ø§ÙØ© Ù…Ø³Ø§ÙØ© Ø¨ÙŠÙ† Ø§Ù„Ø¹Ù†Ø§ØµØ± Ø¯Ø§Ø®Ù„ request-info */
        }

        .admin-name {
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 10px;
        }

        /* ØªÙ†Ø³ÙŠÙ‚ Ø§Ù„Ø£Ø²Ø±Ø§Ø± */
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s ease, color 0.3s ease;
            margin: 5px;
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

        .btn-send-to-council {
            background-color: #ffc107;
            color: #333;
        }

        .btn-display {
            background-color: #17a2b8;
            color: white;
        }

        .btn-accept:hover {
            background-color: #218838;
        }

        .btn-reject:hover {
            background-color: #c82333;
        }

        .btn-send-to-council:hover {
            background-color: #e0a800;
            color: #fff;
        }

        .btn-display:hover {
            background-color: #138496;
        }

        /* ØªÙØ§ØµÙŠÙ„ Ø§Ù„Ø·Ù„Ø¨ */
        .details {
            display: none;
            background-color: #fff;
            color: #333;
            padding: 15px;
            border-radius: 10px;
            margin-top: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
        }

        .show-details {
            display: block;
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
            background-color: #28a745;
            color: #fff;
        }

        .error-message {
            background-color: #dc3545;
            color: #fff;
        }

        /* ØªÙ†Ø³ÙŠÙ‚Ø§Øª Ø­Ø§Ù„Ø© Ø§Ù„Ø·Ù„Ø¨ */
        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 14px;
            font-weight: bold;
            background-color: #d1d1d1; /* Ù„ÙˆÙ† Ø§ÙØªØ±Ø§Ø¶ÙŠ */
            color: #333;
            margin-top: 10px;
        }

        /* Ø­Ø§Ù„Ø© Ø§Ù„Ø¥Ø±Ø³Ø§Ù„ Ø¥Ù„Ù‰ Ù…Ø¬Ù„Ø³ Ø§Ù„ÙƒÙ„ÙŠØ© */
        .status.sent-to-council {
            background-color: #ffd700; /* Ù„ÙˆÙ† Ø£ØµÙØ± Ù„Ø§ÙØª */
            color: #333;
        }

        /* Ø­Ø§Ù„Ø© Ø§Ù„Ù…ÙˆØ§ÙÙ‚Ø© */
        .status.approved {
            background-color: #4caf50; /* Ù„ÙˆÙ† Ø£Ø®Ø¶Ø± */
            color: #fff;
        }

        /* Ø­Ø§Ù„Ø© Ø§Ù„Ø±ÙØ¶ */
        .status.rejected {
            background-color: #f44336; /* Ù„ÙˆÙ† Ø£Ø­Ù…Ø± */
            color: #fff;
        }

        /* Ø²Ø± Ø¥Ø±Ø³Ø§Ù„ Ø¥Ù„Ù‰ Ù…Ø¬Ù„Ø³ Ø§Ù„ÙƒÙ„ÙŠØ© Ø¨Ø¹Ø¯ Ø§Ù„Ø¥Ø±Ø³Ø§Ù„ */
        .btn-send-to-council.sent {
            background-color: #a0a0a0; /* Ù„ÙˆÙ† Ø±Ù…Ø§Ø¯ÙŠ ÙŠØ¯Ù„ Ø¹Ù„Ù‰ Ø§Ù„Ø¥Ø±Ø³Ø§Ù„ */
            color: #666;
            cursor: not-allowed;
            pointer-events: none; /* ÙŠØ¬Ø¹Ù„ Ø§Ù„Ø²Ø± ØºÙŠØ± Ù‚Ø§Ø¨Ù„ Ù„Ù„Ø¶ØºØ· */
            opacity: 0.7; /* ÙŠØ¬Ø¹Ù„ Ø§Ù„Ø²Ø± ÙŠØ¨Ø¯Ùˆ ØºÙŠØ± Ù†Ø´Ø· */
        }

        /* Ù…Ù†Ø¹ ØªØ£Ø«ÙŠØ± Ø§Ù„ØªØ­ÙˆÙŠÙ… Ø¹Ù„Ù‰ Ø§Ù„Ø£Ø²Ø±Ø§Ø± Ø§Ù„Ù…Ø¹Ø·Ù„Ø© */
        .btn-send-to-council.sent:hover {
            background-color: #a0a0a0;
            color: #666;
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
        @media (max-width: 768px) {
            .navbar, .header {
                flex-direction: column;
                align-items: flex-start;
                padding: 10px 20px;
            }

            .logout-btn, .header .nav a {
                margin-top: 10px;
                width: 100%;
                text-align: center;
            }

            .container {
                padding: 15px;
                margin: 80px auto 20px;
            }

            .request-info {
                flex-direction: column;
                align-items: flex-start;
            }

            .btn {
                width: 100%;
                margin: 5px 0;
            }

            .details {
                padding: 10px;
            }

            /* ØªØ­Ø³ÙŠÙ† Ø­Ø¬Ù… Ø§Ù„Ù†ØµÙˆØµ Ù„Ù„Ø­Ø§Ù„Ø§Øª */
            .status {
                font-size: 12px;
                padding: 4px 8px;
            }
        }
    </style>
    <!-- Ù…ÙƒØªØ¨Ø§Øª JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Ø¯Ø§Ù„Ø© Ù„ØªØ­Ù…ÙŠÙ„ Ø§Ù„Ø·Ù„Ø¨Ø§Øª Ø¨Ø´ÙƒÙ„ Ø¯ÙˆØ±ÙŠ
            function loadRequests() {
                $.ajax({
                    url: '../php/get_requests.php', // ØªØ£ÙƒØ¯ Ù…Ù† ÙˆØ¬ÙˆØ¯ Ù‡Ø°Ø§ Ø§Ù„Ù…Ù„Ù ÙˆÙ…Ø¹Ø§Ù„Ø¬Ø© Ø§Ù„Ø·Ù„Ø¨Ø§Øª Ø¨Ø´ÙƒÙ„ ØµØ­ÙŠØ­
                    type: 'GET',
                    success: function(data) {
                        if (data.trim() === '') {
                            $('.container').html(`<p style='color: black; text-align: center;'>Ù„Ø§ ØªÙˆØ¬Ø¯ Ø·Ù„Ø¨Ø§Øª Ù„Ù„Ù…Ø±Ø§Ø¬Ø¹Ø© ÙÙŠ Ø§Ù„ÙˆÙ‚Øª Ø§Ù„Ø­Ø§Ù„ÙŠ.</p>`);
                        } else {
                            $('.container').html(data);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error loading requests:", error);
                    }
                });
            }

            // ØªØ­Ù…ÙŠÙ„ Ø§Ù„Ø·Ù„Ø¨Ø§Øª Ø¹Ù†Ø¯ ØªØ­Ù…ÙŠÙ„ Ø§Ù„ØµÙØ­Ø©
            loadRequests();
            // Ø¥Ø¹Ø§Ø¯Ø© ØªØ­Ù…ÙŠÙ„ Ø§Ù„Ø·Ù„Ø¨Ø§Øª ÙƒÙ„ 30 Ø«Ø§Ù†ÙŠØ©
            setInterval(loadRequests, 30000);

            // Ø¯Ø§Ù„Ø© Ù„Ù…Ø¹Ø§Ù„Ø¬Ø© Ø§Ù„Ù…ÙˆØ§ÙÙ‚Ø© Ø¹Ù„Ù‰ Ø§Ù„Ø·Ù„Ø¨
            window.handleApproval = function(requestId) {
                $.ajax({
                    url: 'admin_dashboard.php',
                    type: 'POST',
                    data: { action: 'approve', request_id: requestId },
                    dataType: 'json',
                    success: function(data) {
                        if (data.showDatePicker) {
                            setExamDate(requestId);
                        } else {
                            removeRequest(requestId);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error approving request:", error);
                    }
                });
            };

            // Ø¯Ø§Ù„Ø© Ù„Ù…Ø¹Ø§Ù„Ø¬Ø© Ø±ÙØ¶ Ø§Ù„Ø·Ù„Ø¨
            window.handleRejection = function(requestId) {
                Swal.fire({
                    title: 'Ø§ÙƒØªØ¨ Ø³Ø¨Ø¨ Ø§Ù„Ø±ÙØ¶',
                    input: 'textarea',
                    inputPlaceholder: 'Ø§Ø¯Ø®Ù„ Ø³Ø¨Ø¨ Ø§Ù„Ø±ÙØ¶ Ù‡Ù†Ø§...',
                    showCancelButton: true,
                    confirmButtonText: 'Ø±ÙØ¶ Ø§Ù„Ø·Ù„Ø¨',
                    cancelButtonText: 'Ø¥Ù„ØºØ§Ø¡',
                    preConfirm: (rejectionReason) => {
                        if (!rejectionReason) {
                            Swal.showValidationMessage('ÙŠØ±Ø¬Ù‰ Ø¥Ø¯Ø®Ø§Ù„ Ø³Ø¨Ø¨ Ø§Ù„Ø±ÙØ¶');
                        }
                        return rejectionReason;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const rejectionReason = result.value;
                        $.ajax({
                            url: 'admin_dashboard.php',
                            type: 'POST',
                            data: {
                                action: 'reject',
                                request_id: requestId,
                                rejection_reason: rejectionReason
                            },
                            dataType: 'json',
                            success: function(data) {
                                removeRequest(requestId);
                                showSuccess();
                            },
                            error: function(xhr, status, error) {
                                console.error("Error rejecting request:", error);
                            }
                        });
                    }
                });
            };

            // Ø¯Ø§Ù„Ø© Ù„ØªØ­Ø¯ÙŠØ¯ ØªØ§Ø±ÙŠØ® Ø§Ù„Ø§Ù…ØªØ­Ø§Ù†
            window.setExamDate = function(requestId) {
                Swal.fire({
                    title: 'Ø­Ø¯Ø¯ ØªØ§Ø±ÙŠØ® Ø§Ù„Ø§Ù…ØªØ­Ø§Ù†',
                    html: '<input type="date" id="examDatePicker" class="swal2-input">',
                    showCancelButton: true,
                    confirmButtonText: 'ØªØ£ÙƒÙŠØ¯',
                    cancelButtonText: 'Ø¥Ù„ØºØ§Ø¡',
                    preConfirm: () => {
                        const examDate = $('#examDatePicker').val();
                        if (!examDate) {
                            Swal.showValidationMessage('ÙŠØ±Ø¬Ù‰ Ø§Ø®ØªÙŠØ§Ø± ØªØ§Ø±ÙŠØ® Ø§Ù„Ø§Ù…ØªØ­Ø§Ù†');
                        }
                        return examDate;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const examDate = result.value;
                        $.ajax({
                            url: 'admin_dashboard.php',
                            type: 'POST',
                            data: {
                                action: 'set_exam_date',
                                request_id: requestId,
                                exam_date: examDate
                            },
                            dataType: 'json',
                            success: function(response) {
                                // ØªØ­Ø¯ÙŠØ« Ù‚Ø§Ø¹Ø¯Ø© Ø§Ù„Ø¨ÙŠØ§Ù†Ø§Øª
                                removeRequest(requestId);  // Ø¥Ø²Ø§Ù„Ø© Ø§Ù„Ø·Ù„Ø¨ Ø¨Ø¹Ø¯ Ø§Ù„ØªØ­Ø¯ÙŠØ« Ø§Ù„Ù†Ø§Ø¬Ø­
                                showSuccess();  // Ø¹Ø±Ø¶ Ø±Ø³Ø§Ù„Ø© Ù†Ø¬Ø§Ø­
                            },
                            error: function(xhr, status, error) {
                                console.error("Error setting exam date:", error);
                            }
                        });
                    }
                });
            };

            // Ø¯Ø§Ù„Ø© Ù„Ø¥Ø±Ø³Ø§Ù„ Ø§Ù„Ø·Ù„Ø¨ Ø¥Ù„Ù‰ Ù…Ø¬Ù„Ø³ Ø§Ù„ÙƒÙ„ÙŠØ©
            window.handleSendToCouncil = function(requestId) {
                Swal.fire({
                    title: 'ØªØ£ÙƒÙŠØ¯ Ø§Ù„Ø¥Ø±Ø³Ø§Ù„ Ø¥Ù„Ù‰ Ù…Ø¬Ù„Ø³ Ø§Ù„ÙƒÙ„ÙŠØ©',
                    text: "Ù‡Ù„ Ø£Ù†Øª Ù…ØªØ£ÙƒØ¯ Ø£Ù†Ùƒ ØªØ±ÙŠØ¯ Ø¥Ø±Ø³Ø§Ù„ Ù‡Ø°Ø§ Ø§Ù„Ø·Ù„Ø¨ Ø¥Ù„Ù‰ Ù…Ø¬Ù„Ø³ Ø§Ù„ÙƒÙ„ÙŠØ©ØŸ",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ù†Ø¹Ù…ØŒ Ø£Ø±Ø³Ù„',
                    cancelButtonText: 'Ù„Ø§ØŒ '
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'admin_dashboard.php',
                            type: 'POST',
                            data: { action: 'Under_Progrees', request_id: requestId },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'ØªÙ… Ø¥Ø±Ø³Ø§Ù„ Ø§Ù„Ø·Ù„Ø¨ Ø¥Ù„Ù‰ Ù…Ø¬Ù„Ø³ Ø§Ù„ÙƒÙ„ÙŠØ© Ø¨Ù†Ø¬Ø§Ø­',
                                        text: 'ØªÙ… ØªØ­Ø¯ÙŠØ« Ø­Ø§Ù„Ø© Ø§Ù„Ø·Ù„Ø¨.',
                                        confirmButtonText: 'Ø­Ø³Ù†Ù‹Ø§'
                                    });
                                    // ØªØ­Ø¯ÙŠØ« Ø­Ø§Ù„Ø© Ø§Ù„Ø·Ù„Ø¨ ÙÙŠ ÙˆØ§Ø¬Ù‡Ø© Ø§Ù„Ù…Ø³ØªØ®Ø¯Ù…
                                    $('#request-' + requestId).find('.status')
                                        .text('ØªÙ… Ø§Ù„Ø¥Ø±Ø³Ø§Ù„ Ø¥Ù„Ù‰ Ù…Ø¬Ù„Ø³ Ø§Ù„ÙƒÙ„ÙŠØ©')
                                        .removeClass('approved rejected')
                                        .addClass('sent-to-council');
                                    // ØªØºÙŠÙŠØ± Ù„ÙˆÙ† Ø²Ø± Ø§Ù„Ø¥Ø±Ø³Ø§Ù„ ÙˆØ¥Ù„ØºØ§Ø¡ Ø¥Ù…ÙƒØ§Ù†ÙŠØ© Ø§Ù„Ø¶ØºØ· Ø¹Ù„ÙŠÙ‡
                                    var sendButton = $('#request-' + requestId).find('.btn-send-to-council');
                                    sendButton.addClass('sent'); // Ø¥Ø¶Ø§ÙØ© ÙØ¦Ø© 'sent' Ù„ØªØ·Ø¨ÙŠÙ‚ Ø§Ù„ØªÙ†Ø³ÙŠÙ‚Ø§Øª Ø§Ù„Ø¬Ø¯ÙŠØ¯Ø©
                                    sendButton.prop('disabled', true); // ØªØ¹Ø·ÙŠÙ„ Ø§Ù„Ø²Ø±
                                } else {
                                    Swal.fire('Ø­Ø¯Ø« Ø®Ø·Ø£: ' + response.error);
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error("Error sending to council:", error);
                                Swal.fire('Ø­Ø¯Ø« Ø®Ø·Ø£ Ø£Ø«Ù†Ø§Ø¡ Ø¥Ø±Ø³Ø§Ù„ Ø§Ù„Ø·Ù„Ø¨');
                            }
                        });
                    }
                });
            };

            // Ø¯Ø§Ù„Ø© Ù„Ø¥Ø²Ø§Ù„Ø© Ø§Ù„Ø·Ù„Ø¨ Ù…Ù† Ø§Ù„Ù‚Ø§Ø¦Ù…Ø© Ø¨Ø¹Ø¯ Ø§Ù„Ù…ÙˆØ§ÙÙ‚Ø© Ø£Ùˆ Ø§Ù„Ø±ÙØ¶
            function removeRequest(requestId) {
                $('#request-' + requestId).fadeOut(400, function() {
                    $(this).remove();
                    if ($('.request').length === 0) {
                        $('.container').html("<p style='color: black; text-align: center;'>Ù„Ø§ ØªÙˆØ¬Ø¯ Ø·Ù„Ø¨Ø§Øª Ù„Ù„Ù…Ø±Ø§Ø¬Ø¹Ø© ÙÙŠ Ø§Ù„ÙˆÙ‚Øª Ø§Ù„Ø­Ø§Ù„ÙŠ.</p>");
                    }
                });
            }

            // Ø¯Ø§Ù„Ø© Ù„Ø¹Ø±Ø¶ Ø±Ø³Ø§Ù„Ø© Ù†Ø¬Ø§Ø­
            function showSuccess() {
                Swal.fire({
                    icon: 'success',
                    title: 'ØªÙ… ØªØ­Ø¯ÙŠØ« Ø§Ù„Ø·Ù„Ø¨ Ø¨Ù†Ø¬Ø§Ø­',
                    showConfirmButton: false,
                    timer: 1500
                });
            }

            // Ø¯Ø§Ù„Ø© Ù„ØªØ¨Ø¯ÙŠÙ„ Ø¹Ø±Ø¶ ØªÙØ§ØµÙŠÙ„ Ø§Ù„Ø·Ù„Ø¨
            window.toggleDetails = function(id) {
                $('#details-' + id).toggleClass('show-details');
            };
        });
    </script>
</head>
<body>

    <!-- Ø§Ù„Ù‡ÙŠØ¯Ø± (Header) -->
    <header class="header navbar">
        <?php echo "<div class='admin-name'> Ù…Ø±Ø­Ø¨Ø§: " . htmlspecialchars($current_admin_name) . "</div>"; ?>
        <div class="nav">
            <a href="../php/logout.php">ØªØ³Ø¬ÙŠÙ„ Ø§Ù„Ø®Ø±ÙˆØ¬</a>
        </div>
    </header>

    <!-- Ù…Ø­ØªÙˆÙ‰ Ø§Ù„ØµÙØ­Ø© -->
    <div class="container">
        <h2>Ø¥Ø¯Ø§Ø±Ø© Ø§Ù„Ø·Ù„Ø¨Ø§Øª</h2>
    <?php

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<div id='request-" . $row['id'] . "' class='request'>";
            echo "<div class='request-info'>";
            echo "<span><strong>Ø§Ø³Ù… Ø§Ù„Ø·Ø§Ù„Ø¨:</strong> " . htmlspecialchars($row['student_name']) . "</span>";
            echo "<span><strong>Ø±Ù‚Ù… Ø§Ù„Ø¬Ø§Ù…Ø¹ÙŠ:</strong> " . htmlspecialchars($row['student_id']) . "</span>";
            echo "<span><strong>Ø±Ù‚Ù… Ø§Ù„Ø·Ù„Ø¨:</strong> " . htmlspecialchars($row['id']) . "</span>";
            echo "<span><strong>ØªØ§Ø±ÙŠØ® Ø§Ù„Ø·Ù„Ø¨:</strong> " . htmlspecialchars($row['created_at']) . "</span>";
            echo "<button class='btn btn-display' onclick='toggleDetails(" . $row['id'] . ")'>Ø¹Ø±Ø¶ Ø§Ù„Ø¹Ø°Ø±</button>";
            echo "</div>";

            echo "<div id='details-" . $row['id'] . "' class='details'>";
            echo "<p><strong>Ø§Ù„Ù…Ø§Ø¯Ø©:</strong> " . htmlspecialchars($row['class']) . "</p>";
            echo "<p><strong>Ø§Ù„ÙˆØµÙ:</strong> " . htmlspecialchars($row['description']) . "</p>";
            echo "<p><strong>Ø§Ù„Ø¹Ø°Ø±:</strong> " . htmlspecialchars($row['excuse']) . "</p>";
            echo "<p><strong>ØªØ§Ø±ÙŠØ® Ø§Ù„ØºÙŠØ§Ø¨:</strong> " . htmlspecialchars($row['date']) . "</p>";
            echo "<p><strong>Ø±Ù‚Ù… Ø§Ù„Ø´Ø¹Ø¨Ø©:</strong> " . htmlspecialchars($row['section_number']) . "</p>";

            if ($row['file_path']) {
                echo "<p><strong>Ù…Ù„Ù Ù…Ø±ÙÙ‚:</strong> <a href='../php/view_file.php?file=" . urlencode($row['file_path']) . "' target='_blank'>Ø¹Ø±Ø¶ Ø§Ù„Ù…Ù„Ù</a></p>";
            }

            echo "<p class='status'><strong>Ø§Ù„Ø­Ø§Ù„Ø©:</strong> " . htmlspecialchars($row['status']) . "</p>";

            echo "<div class='actions'>";

            // Ø¹Ø±Ø¶ Ø£Ø²Ø±Ø§Ø± Ø§Ù„Ù‚Ø¨ÙˆÙ„ ÙˆØ§Ù„Ø±ÙØ¶ Ù„Ø¬Ù…ÙŠØ¹ Ø§Ù„Ù…Ø³Ø¤ÙˆÙ„ÙŠÙ† Ø¨Ø§Ø³ØªØ«Ù†Ø§Ø¡ Ø§Ù„Ù…Ø³Ø¤ÙˆÙ„ Ø§Ù„Ø«Ø§Ù„Ø«
            if ($current_admin_id != 3) {
                echo "<button type='button' class='btn btn-accept' onclick='handleApproval(" . $row['id'] . ")'>Ù‚Ø¨ÙˆÙ„</button>";
                echo "<button type='button' class='btn btn-reject' onclick='handleRejection(" . $row['id'] . ")'>Ø±ÙØ¶</button>";
            }

            // Ø¹Ø±Ø¶ Ø²Ø± "Ø¥Ø±Ø³Ø§Ù„ Ø¥Ù„Ù‰ Ù…Ø¬Ù„Ø³ Ø§Ù„ÙƒÙ„ÙŠØ©" ÙÙ‚Ø· Ù„Ù„Ù…Ø³Ø¤ÙˆÙ„ÙŠÙ† ÙÙŠ Ø¬Ø¯ÙˆÙ„ 'admins'
            if ($current_admin_table == 'admins') {
                echo "<div class='send-to-council-box'>";
                echo "<button type='button' class='btn btn-send-to-council' onclick='handleSendToCouncil(" . $row['id'] . ")'>Ø¥Ø±Ø³Ø§Ù„ Ø¥Ù„Ù‰ Ù…Ø¬Ù„Ø³ Ø§Ù„ÙƒÙ„ÙŠØ©</button>";
                echo "</div>";
            }

            // Ø¹Ø±Ø¶ Ø²Ø± "ØªØ­Ø¯ÙŠØ¯ ØªØ§Ø±ÙŠØ® Ø§Ù„Ø§Ù…ØªØ­Ø§Ù†" ÙÙ‚Ø· Ù„Ù„Ù…Ø³Ø¤ÙˆÙ„ Ø§Ù„Ø«Ø§Ù„Ø«
            if ($current_admin_id == 3) {
                echo "<button type='button' class='btn btn-accept' onclick='setExamDate(" . $row['id'] . ")'>ØªØ­Ø¯ÙŠØ¯ ØªØ§Ø±ÙŠØ® Ø§Ù„Ø§Ù…ØªØ­Ø§Ù†</button>";
            }

            echo "</div>"; // Ù†Ù‡Ø§ÙŠØ© div Ø§Ù„Ø®Ø§Øµ Ø¨Ø§Ù„Ø£Ø²Ø±Ø§Ø±
            echo "</div>"; // Ù†Ù‡Ø§ÙŠØ© div Ø§Ù„Ø®Ø§Øµ Ø¨Ø§Ù„ØªÙØ§ØµÙŠÙ„
         
            echo "</div>"; // Ù†Ù‡Ø§ÙŠØ© div Ø§Ù„Ø®Ø§Øµ Ø¨Ø§Ù„Ø·Ù„Ø¨
        }
    } else {
        echo "<p style='color: black; text-align: center;'>Ù„Ø§ ØªÙˆØ¬Ø¯ Ø·Ù„Ø¨Ø§Øª Ù„Ù„Ù…Ø±Ø§Ø¬Ø¹Ø© ÙÙŠ Ø§Ù„ÙˆÙ‚Øª Ø§Ù„Ø­Ø§Ù„ÙŠ.</p>";
    }
    ?>

    </div>

    <!-- Ø§Ù„ÙÙˆØªØ± (Footer) -->
    <footer>
        <a href="#">Ø­ÙˆÙ„</a>
        <a href="#">ØªÙˆØ§ØµÙ„ Ù…Ø¹Ù†Ø§</a>
        <p>&copy; 2024 Ø¨ÙˆØ§Ø¨Ø© ØªØ³Ù‡ÙŠÙ„. Ø¬Ù…ÙŠØ¹ Ø§Ù„Ø­Ù‚ÙˆÙ‚ Ù…Ø­ÙÙˆØ¸Ø©.</p>
    </footer>

</body>
</html>

