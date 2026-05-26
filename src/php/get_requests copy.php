<?php
include '../includes/db_connection.php'; 
session_start();

$current_admin_id = $_SESSION['user_id'];
$current_admin_table = $_SESSION['user_table']; // Ø§Ø³ØªØ®Ø¯Ù… Ù…ØªØºÙŠØ± Ù„ØªØ­Ø¯ÙŠØ¯ Ø¥Ø°Ø§ ÙƒØ§Ù† Ø§Ù„Ù…Ø³Ø¤ÙˆÙ„ Ù…ÙˆØ¬ÙˆØ¯Ù‹Ø§ ÙÙŠ Ø¬Ø¯ÙˆÙ„ "admins" Ø£Ùˆ "users"

// Ø¬Ù„Ø¨ Ø§Ù„Ù‚Ø³Ù… Ø§Ù„Ø­Ø§Ù„ÙŠ Ù„Ù„Ù…Ø³Ø¤ÙˆÙ„
if ($current_admin_table == 'users') {
    // Ø§Ù„Ù…Ø³Ø¤ÙˆÙ„ Ø§Ù„Ø£ÙˆÙ„ ÙŠÙƒÙˆÙ† ÙÙŠ Ø¬Ø¯ÙˆÙ„ users
    $sql_admin_department = "SELECT department_id FROM users WHERE id = ?";
} else {
    // Ø§Ù„Ù…Ø³Ø¤ÙˆÙ„ Ø§Ù„Ø«Ø§Ù†ÙŠ ÙˆØ§Ù„Ø«Ø§Ù„Ø« ÙŠÙƒÙˆÙ†ÙˆÙ† ÙÙŠ Ø¬Ø¯ÙˆÙ„ admins
    $sql_admin_department = "SELECT department_id FROM admins WHERE id = ?";
}

$stmt_admin_department = $conn->prepare($sql_admin_department);
$stmt_admin_department->bind_param("i", $current_admin_id);
$stmt_admin_department->execute();
$result_admin_department = $stmt_admin_department->get_result();

if ($result_admin_department->num_rows > 0) {
    $admin_row = $result_admin_department->fetch_assoc();
    $admin_department_id = $admin_row['department_id'];
} else {
    die("Ø®Ø·Ø£ ÙÙŠ Ø¬Ù„Ø¨ Ù‚Ø³Ù… Ø§Ù„Ù…Ø³Ø¤ÙˆÙ„.");
}

// Ø¬Ù„Ø¨ Ø§Ù„Ø·Ù„Ø¨Ø§Øª Ø§Ù„ØªÙŠ ØªÙƒÙˆÙ† Ø­Ø§Ù„ØªÙ‡Ø§ 'Pending' ÙˆØ§Ù„Ù…Ø®ØµØµØ© Ù„Ù„Ù…Ø³Ø¤ÙˆÙ„ Ø§Ù„Ø­Ø§Ù„ÙŠ ÙˆØªÙ†ØªÙ…ÙŠ Ù„Ù†ÙØ³ Ø§Ù„Ù‚Ø³Ù…
$sql_requests = "SELECT requests.*, students.name AS student_name, students.department_id AS student_department
FROM requests
JOIN students ON requests.student_id = students.id
WHERE requests.status NOT IN ('Approved', 'Rejected')
AND requests.current_admin = ?
AND requests.current_admin_table = ?
AND students.department_id = ?";  // Ø¥Ø¶Ø§ÙØ© Ø´Ø±Ø· Ø§Ù„ØªØ­Ù‚Ù‚ Ù…Ù† Ø§Ù„Ù‚Ø³Ù…

$stmt_requests = $conn->prepare($sql_requests);
$stmt_requests->bind_param("isi", $current_admin_id, $current_admin_table, $admin_department_id);

$stmt_requests->execute();
$result = $stmt_requests->get_result();

if (!$result) {
    die("Error executing query: " . $conn->error);
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
            echo "<p><strong>Ù…Ù„Ù Ù…Ø±ÙÙ‚:</strong> <a href='view_file.php?file=" . urlencode($row['file_path']) . "' target='_blank'>Ø¹Ø±Ø¶ Ø§Ù„Ù…Ù„Ù</a></p>";
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
        echo "<div id='success-" . $row['id'] . "' class='success-message'>ØªÙ…Øª Ø§Ù„Ù…ÙˆØ§ÙÙ‚Ø©</div>";
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

