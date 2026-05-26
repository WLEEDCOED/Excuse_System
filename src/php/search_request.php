<?php
session_start();
include '../includes/db_connection.php';

// Ø§Ù„ØªØ£ÙƒØ¯ Ù…Ù† ØªØ³Ø¬ÙŠÙ„ Ø§Ù„Ø¯Ø®ÙˆÙ„ ÙƒØ·Ø§Ù„Ø¨
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != '3') {
    header('Location: ../pages/login.php');
    exit;
}

// Ø¬Ù„Ø¨ Ù…Ø¹Ø±Ù Ø§Ù„Ø·Ø§Ù„Ø¨ Ù…Ù† Ø§Ù„Ø¬Ù„Ø³Ø©
$student_id = $_SESSION['user_id'];

// Ø§Ù„ØªØ£ÙƒØ¯ Ù…Ù† Ø£Ù† Ø±Ù‚Ù… Ø§Ù„Ø·Ù„Ø¨ ØªÙ… Ø¥Ø±Ø³Ø§Ù„Ù‡
if (isset($_POST['request_id']) && !empty($_POST['request_id'])) {
    $request_id = intval($_POST['request_id']);

    // Ø§Ø³ØªØ¹Ù„Ø§Ù… Ø¬Ù„Ø¨ Ø§Ù„Ø·Ù„Ø¨ Ø¨Ù†Ø§Ø¡Ù‹ Ø¹Ù„Ù‰ Ù…Ø¹Ø±Ù Ø§Ù„Ø·Ø§Ù„Ø¨ ÙˆØ±Ù‚Ù… Ø§Ù„Ø·Ù„Ø¨
    $sql = "SELECT * FROM requests WHERE student_id = ? AND id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $student_id, $request_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
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
        } elseif ($row['status'] === 'Sent to Council') {
            echo "<p>Ø§Ù„Ø­Ø§Ù„Ø©: Ù‚ÙŠØ¯ Ø§Ù„Ù…Ø±Ø§Ø¬Ø¹Ø© Ù…Ù† Ù…Ø¬Ù„Ø³ Ø§Ù„ÙƒÙ„ÙŠØ©</p>";
        } else {
            echo "<p>Ø§Ù„Ø­Ø§Ù„Ø©: " . htmlspecialchars($row['status']) . "</p>";
        }

        echo "</div>";
    } else {
        echo "<p>Ù„Ù… ÙŠØªÙ… Ø§Ù„Ø¹Ø«ÙˆØ± Ø¹Ù„Ù‰ Ø§Ù„Ø·Ù„Ø¨.</p>";
    }
} else {
    echo "<p>ÙŠØ±Ø¬Ù‰ Ø¥Ø¯Ø®Ø§Ù„ Ø±Ù‚Ù… Ø·Ù„Ø¨ ØµØ§Ù„Ø­.</p>";
}
?>

