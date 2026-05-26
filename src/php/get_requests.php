<?php
include '../includes/db_connection.php'; 
session_start();

// Ø§Ù„ØªØ­Ù‚Ù‚ Ù…Ù† ØªØ³Ø¬ÙŠÙ„ Ø§Ù„Ø¯Ø®ÙˆÙ„
if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.php");
    exit();
}

// Ø¬Ù„Ø¨ Ù…Ø¹Ø±Ù Ø§Ù„Ù…Ø³Ø¤ÙˆÙ„ Ø§Ù„Ø­Ø§Ù„ÙŠ ÙˆØ¬Ø¯ÙˆÙ„Ù‡
$current_admin_id = $_SESSION['user_id'];
$current_admin_table = $_SESSION['table']; // ØªØ­Ø¯ÙŠØ¯ Ø¬Ø¯ÙˆÙ„ Ø§Ù„Ù…Ø³Ø¤ÙˆÙ„


$sql_requests = "SELECT final_exam.*, students.name AS student_name, department.name AS department_name
    FROM final_exam
    JOIN students ON final_exam.student_id = students.id
    LEFT JOIN department ON students.department_id = department.id
    WHERE final_exam.status NOT IN ('Approved', 'Rejected')
    AND final_exam.current_admin = ?
    AND final_exam.current_admin_table = ?";

$stmt_requests = $conn->prepare($sql_requests);
if (!$stmt_requests) {
    die("Ø®Ø·Ø£ ÙÙŠ ØªØ­Ø¶ÙŠØ± Ø§Ù„Ø§Ø³ØªØ¹Ù„Ø§Ù…: " . $conn->error);
}

$stmt_requests->bind_param("is", $current_admin_id, $current_admin_table);

if (!$stmt_requests->execute()) {
    die("Ø®Ø·Ø£ ÙÙŠ ØªÙ†ÙÙŠØ° Ø§Ù„Ø§Ø³ØªØ¹Ù„Ø§Ù…: " . $stmt_requests->error);
}

$result = $stmt_requests->get_result();
if (!$result) {
    die("Ø®Ø·Ø£ ÙÙŠ Ø§Ù„Ø­ØµÙˆÙ„ Ø¹Ù„Ù‰ Ø§Ù„Ù†ØªØ§Ø¦Ø¬: " . $stmt_requests->error);
}

// Ø¹Ø±Ø¶ Ø§Ù„Ø¹Ù†ÙˆØ§Ù† Ø§Ù„Ø±Ø¦ÙŠØ³ÙŠ
echo "<h2>Ø¥Ø¯Ø§Ø±Ø© Ø§Ù„Ø·Ù„Ø¨Ø§Øª</h2>";

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<div id='request-" . $row['id'] . "' class='request'>";
        echo "<div class='request-info'>";
        echo "<span><strong>Ø§Ø³Ù… Ø§Ù„Ø·Ø§Ù„Ø¨:</strong> " . htmlspecialchars($row['student_name']) . "</span>";
        echo "<span><strong>Ø±Ù‚Ù… Ø§Ù„Ø¬Ø§Ù…Ø¹ÙŠ:</strong> " . htmlspecialchars($row['student_id']) . "</span>";
        echo "<span><strong>Ø±Ù‚Ù… Ø§Ù„Ø·Ù„Ø¨:</strong> " . htmlspecialchars($row['id']) . "</span>";

        // ØªÙ†Ø³ÙŠÙ‚ Ø¹Ø±Ø¶ ØªØ§Ø±ÙŠØ® Ø§Ù„Ø·Ù„Ø¨ Ù„ÙŠÙƒÙˆÙ† ÙÙ‚Ø· ØªØ§Ø±ÙŠØ® Ø¨Ø¯ÙˆÙ† Ø§Ù„Ø³Ø§Ø¹Ø©
        $created_at_date = date("Y-m-d", strtotime($row['created_at']));
        echo "<span><strong>ØªØ§Ø±ÙŠØ® Ø§Ù„Ø·Ù„Ø¨:</strong> " . htmlspecialchars($created_at_date) . "</span>";

        echo "<span><strong>Ù‚Ø³Ù… Ø§Ù„Ø·Ø§Ù„Ø¨:</strong> " . htmlspecialchars($row['department_name']) . "</span>"; // Ø¹Ø±Ø¶ Ù‚Ø³Ù… Ø§Ù„Ø·Ø§Ù„Ø¨
        echo "<button class='btn btn-display' onclick='toggleDetails(" . $row['id'] . ")'>Ø¹Ø±Ø¶ Ø§Ù„Ø¹Ø°Ø±</button>";
        echo "</div>";
        
        echo "<div id='details-" . $row['id'] . "' class='details'>";
        echo "<p><strong>Ø§Ù„Ù…Ø§Ø¯Ø©:</strong> " . htmlspecialchars($row['class']) . "</p>";
        echo "<p><strong>Ø§Ù„ÙˆØµÙ:</strong> " . htmlspecialchars($row['description']) . "</p>";
        echo "<p><strong>Ø§Ù„Ø¹Ø°Ø±:</strong> " . htmlspecialchars($row['excuse']) . "</p>";
        echo "<p><strong>ØªØ§Ø±ÙŠØ® Ø§Ù„ØºÙŠØ§Ø¨:</strong> " . htmlspecialchars($row['date']) . "</p>";

        if ($row['file_path']) {
            echo "<p><strong>Ù…Ù„Ù Ù…Ø±ÙÙ‚:</strong> <a href='view_file.php?file=" . urlencode($row['file_path']) . "' target='_blank'>Ø¹Ø±Ø¶ Ø§Ù„Ù…Ù„Ù</a></p>";
        }

        echo "<div class='actions'>";
        if ($current_admin_id != 3) {
            // Ø¹Ø±Ø¶ Ø£Ø²Ø±Ø§Ø± Ø§Ù„Ù‚Ø¨ÙˆÙ„ ÙˆØ§Ù„Ø±ÙØ¶ Ù„Ø¬Ù…ÙŠØ¹ Ø§Ù„Ù…Ø³Ø¤ÙˆÙ„ÙŠÙ† Ø¨Ø§Ø³ØªØ«Ù†Ø§Ø¡ Ø§Ù„Ù…Ø³Ø¤ÙˆÙ„ Ø§Ù„Ø«Ø§Ù„Ø«
            echo "<button type='button' class='btn btn-accept' onclick='handleApproval(" . $row['id'] . ")'>Ù‚Ø¨ÙˆÙ„</button>";
            echo "<button type='button' class='btn btn-reject' onclick='handleRejection(" . $row['id'] . ")'>Ø±ÙØ¶</button>";
        }
        if ($current_admin_id == 2) {
            // Ø¹Ø±Ø¶ Ø²Ø± Ø¥Ø±Ø³Ø§Ù„ Ø¥Ù„Ù‰ Ù…Ø¬Ù„Ø³ Ø§Ù„ÙƒÙ„ÙŠØ© Ù„Ù„Ù…Ø³Ø¤ÙˆÙ„ Ø§Ù„Ø«Ø§Ù†ÙŠ
            echo "<div class='send-to-council-box'>";
            echo "<button class='btn btn-send-to-council' onclick='handleSendToCouncil(" . $row['id'] . ")'>Ø¥Ø±Ø³Ø§Ù„ Ø¥Ù„Ù‰ Ù…Ø¬Ù„Ø³ Ø§Ù„ÙƒÙ„ÙŠØ©</button>";
            echo "</div>";
        }
        if ($current_admin_id == 3) {
            // Ø¹Ø±Ø¶ Ø²Ø± ØªØ­Ø¯ÙŠØ¯ ØªØ§Ø±ÙŠØ® Ø§Ù„Ø§Ù…ØªØ­Ø§Ù† Ù„Ù„Ù…Ø³Ø¤ÙˆÙ„ Ø§Ù„Ø«Ø§Ù„Ø« ÙÙ‚Ø·
            echo "<button class='btn btn-accept' onclick='setExamDate(" . $row['id'] . ")'>ØªØ­Ø¯ÙŠØ¯ ØªØ§Ø±ÙŠØ® Ø§Ù„Ø§Ù…ØªØ­Ø§Ù†</button>";
        }

        echo "</div>"; // Ù†Ù‡Ø§ÙŠØ© div Ø§Ù„Ø®Ø§Øµ Ø¨Ø§Ù„Ø£Ø²Ø±Ø§Ø±
        echo "</div>"; // Ù†Ù‡Ø§ÙŠØ© div Ø§Ù„Ø®Ø§Øµ Ø¨Ø§Ù„ØªÙØ§ØµÙŠÙ„

        echo "</div>"; // Ù†Ù‡Ø§ÙŠØ© div Ø§Ù„Ø®Ø§Øµ Ø¨Ø§Ù„Ø·Ù„Ø¨
    }
} else {
    echo "<p style='color: black; text-align: center;'>Ù„Ø§ ØªÙˆØ¬Ø¯ Ø·Ù„Ø¨Ø§Øª Ù„Ù„Ù…Ø±Ø§Ø¬Ø¹Ø© ÙÙŠ Ø§Ù„ÙˆÙ‚Øª Ø§Ù„Ø­Ø§Ù„ÙŠ.</p>";
}
?>

