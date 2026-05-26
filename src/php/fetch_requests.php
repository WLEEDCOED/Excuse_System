<?php
session_start();
include '../includes/db_connection.php';

// Ø§Ù„ØªØ£ÙƒØ¯ Ù…Ù† ØªØ³Ø¬ÙŠÙ„ Ø§Ù„Ø¯Ø®ÙˆÙ„
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != '3') {
    echo 'ÙŠØ±Ø¬Ù‰ ØªØ³Ø¬ÙŠÙ„ Ø§Ù„Ø¯Ø®ÙˆÙ„';
    exit;
}

$student_id = $_SESSION['user_id'];

// Ø¬Ù„Ø¨ Ù†ÙˆØ¹ Ø§Ù„Ø·Ù„Ø¨Ø§Øª Ø§Ù„Ù…Ø·Ù„ÙˆØ¨Ø© (Ù†Ù‡Ø§Ø¦ÙŠ Ø£Ùˆ ÙŠÙˆÙ…ÙŠ)
$request_type = $_POST['request_type'] ?? 'final_excuses';

// Ø§Ø³ØªØ¹Ù„Ø§Ù… Ø·Ù„Ø¨Ø§Øª Ø§Ù„Ø£Ø¹Ø°Ø§Ø± Ø§Ù„Ù†Ù‡Ø§Ø¦ÙŠØ© Ù…Ù† Ø¬Ø¯ÙˆÙ„ `requests`
if ($request_type === 'final_excuses') {
    $sql = "SELECT r.*, u.name AS user_name, a.name AS admin_name
            FROM requests AS r
            JOIN users AS u ON r.student_id = u.id
            JOIN admins AS a ON r.current_admin = a.id
            WHERE r.student_id = ? AND r.type = 'final_excuse'
            ORDER BY r.id ASC";
}

// Ø§Ø³ØªØ¹Ù„Ø§Ù… Ø§Ù„Ø·Ù„Ø¨Ø§Øª Ø§Ù„ÙŠÙˆÙ…ÙŠØ© Ù…Ù† Ø¬Ø¯ÙˆÙ„ `lecture_excuses`
else {
    $sql = "SELECT le.*, p.name AS professor_name, c.name AS class_name
            FROM lecture_excuses AS le
            JOIN professors AS p ON le.professor_id = p.id
            JOIN classes AS c ON le.lectures_id = c.id
            WHERE le.student_id = ?
            ORDER BY le.id ASC";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

// Ø¹Ø±Ø¶ Ø§Ù„Ø·Ù„Ø¨Ø§Øª Ø¨Ù†Ø§Ø¡Ù‹ Ø¹Ù„Ù‰ Ø§Ù„Ù†ÙˆØ¹ Ø§Ù„Ù…Ø®ØªØ§Ø±
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<div class='request' id='request-" . $row['id'] . "'>";
        echo "<h3>Ø·Ù„Ø¨ Ø±Ù‚Ù…: " . $row['id'] . "</h3>";

        // Ø¥Ø°Ø§ ÙƒØ§Ù†Øª Ø·Ù„Ø¨Ø§Øª Ø£Ø¹Ø°Ø§Ø± Ù†Ù‡Ø§Ø¦ÙŠØ©
        if ($request_type === 'final_excuses') {
            if ($row['status'] == 'approved') {
                if (!empty($row['exam_date']) && $row['exam_date'] != '0000-00-00') {
                    echo "<p>ØªÙ… Ù‚Ø¨ÙˆÙ„ Ø·Ù„Ø¨Ùƒ. Ù…ÙˆØ¹Ø¯ Ø§Ù„Ø§Ø®ØªØ¨Ø§Ø± Ù‡Ùˆ: " . htmlspecialchars($row['exam_date']) . "</p>";
                } else {
                    echo "<p>ØªÙ… Ù‚Ø¨ÙˆÙ„ Ø·Ù„Ø¨Ùƒ. Ù…ÙˆØ¹Ø¯ Ø§Ù„Ø§Ø®ØªØ¨Ø§Ø± Ø³ÙŠØªÙ… ØªØ­Ø¯ÙŠØ¯Ù‡ Ù‚Ø±ÙŠØ¨Ù‹Ø§.</p>";
                }
            } elseif ($row['status'] == 'rejected') {
                echo "<p class='status-rejected'>ØªÙ… Ø±ÙØ¶ Ø·Ù„Ø¨Ùƒ.</p>";
                if (!empty($row['rejection_reason'])) {
                    echo "<p>Ø³Ø¨Ø¨ Ø§Ù„Ø±ÙØ¶: " . htmlspecialchars($row['rejection_reason']) . "</p>";
                }
            } else {
                echo "<p>Ø§Ù„Ø­Ø§Ù„Ø©: " . htmlspecialchars($row['status']) . "</p>";
            }
            echo "<p>Ø§Ù„Ø·Ù„Ø¨ Ø¹Ù†Ø¯ Ø§Ù„Ù…Ø³Ø¤ÙˆÙ„: " . htmlspecialchars($row['admin_name']) . "</p>";
        }

        // Ø¥Ø°Ø§ ÙƒØ§Ù†Øª Ø·Ù„Ø¨Ø§Øª ÙŠÙˆÙ…ÙŠØ©
        else {
            echo "<p>Ø§Ù„Ø£Ø³ØªØ§Ø° Ø§Ù„Ù…Ø³Ø¤ÙˆÙ„: " . htmlspecialchars($row['professor_name']) . "</p>";
            echo "<p>Ø§Ù„Ù…Ø§Ø¯Ø©: " . htmlspecialchars($row['class_name']) . "</p>";
            echo "<p>Ø§Ù„ÙˆØµÙ: " . htmlspecialchars($row['description']) . "</p>";
            echo "<p>Ø­Ø§Ù„Ø© Ø§Ù„Ø·Ù„Ø¨: " . htmlspecialchars($row['status']) . "</p>";
            echo "<p>ØªØ§Ø±ÙŠØ® Ø§Ù„ØºÙŠØ§Ø¨: " . htmlspecialchars($row['date']) . "</p>";
            echo "<p>Ø±Ù‚Ù… Ø§Ù„Ø´Ø¹Ø¨Ø©: " . htmlspecialchars($row['section_number']) . "</p>";
        }

        echo "</div>";
    }
} else {
    echo "<p>Ù„Ø§ ØªÙˆØ¬Ø¯ Ø·Ù„Ø¨Ø§Øª.</p>";
}

