<?php
include '../includes/db_connection.php';

if (isset($_GET['request_id']) && isset($_GET['exam_date'])) {
    $request_id = intval($_GET['request_id']);
    $exam_date = $conn->real_escape_string($_GET['exam_date']);

    // ØªØ­Ù‚Ù‚ Ù…Ù† ØµØ­Ø© ØªØ§Ø±ÙŠØ® Ø§Ù„Ø§Ø®ØªØ¨Ø§Ø± (ÙŠÙ…ÙƒÙ†Ùƒ Ø¥Ø¶Ø§ÙØ© Ù…Ø²ÙŠØ¯ Ù…Ù† Ø§Ù„ØªØ­Ù‚Ù‚ Ù‡Ù†Ø§)
    $date_format = 'Y-m-d H:i:s';
    $d = DateTime::createFromFormat($date_format, $exam_date);
    if (!($d && $d->format($date_format) === $exam_date)) {
        die("Ø§Ù„ØªØ§Ø±ÙŠØ® ØºÙŠØ± ØµØ§Ù„Ø­. Ø§Ù„Ø±Ø¬Ø§Ø¡ Ø§Ø³ØªØ®Ø¯Ø§Ù… Ø§Ù„ØªÙ†Ø³ÙŠÙ‚ YYYY-MM-DD HH:MM:SS.");
    }

    // ØªØ­Ø¯ÙŠØ« Ù…ÙˆØ¹Ø¯ Ø§Ù„Ø§Ø®ØªØ¨Ø§Ø± ÙˆØ­Ø§Ù„Ø© Ø§Ù„Ø·Ù„Ø¨ ÙÙŠ Ù‚Ø§Ø¹Ø¯Ø© Ø§Ù„Ø¨ÙŠØ§Ù†Ø§Øª
    $sql = "UPDATE requests SET status='Approved', exam_date='$exam_date' WHERE id=$request_id";
    
    if ($conn->query($sql) === TRUE) {
        // Ø¬Ù„Ø¨ Ø¨ÙŠØ§Ù†Ø§Øª Ø§Ù„Ø·Ø§Ù„Ø¨ Ù„Ø¥Ø±Ø³Ø§Ù„ Ø±Ø³Ø§Ù„Ø© ØªØ£ÙƒÙŠØ¯
        $sql_request = "SELECT student_name, student_email FROM requests WHERE id=$request_id";
        $request_result = $conn->query($sql_request);
        if ($request_result && $request_result->num_rows > 0) {
            $request_row = $request_result->fetch_assoc(); 
            $student_name = $request_row['student_name'];
            $student_email = $request_row['student_email'];

            // Ø¥Ø¹Ø¯Ø§Ø¯ Ø§Ù„Ø¨Ø±ÙŠØ¯ Ø§Ù„Ø¥Ù„ÙƒØªØ±ÙˆÙ†ÙŠ
            $to = $student_email;
            $subject = "ØªÙ…Øª Ø§Ù„Ù…ÙˆØ§ÙÙ‚Ø© Ø¹Ù„Ù‰ Ø·Ù„Ø¨Ùƒ ÙˆØªØ­Ø¯ÙŠØ¯ Ù…ÙˆØ¹Ø¯ Ø§Ù„Ø§Ø®ØªØ¨Ø§Ø±";
            $message = "Ø¹Ø²ÙŠØ²ÙŠ $student_name,\n\nØªÙ‡Ø§Ù†ÙŠÙ†Ø§! ØªÙ… Ø§Ù„Ù…ÙˆØ§ÙÙ‚Ø© Ø¹Ù„Ù‰ Ø·Ù„Ø¨Ùƒ.\nÙ…ÙˆØ¹Ø¯ Ø§Ù„Ø§Ø®ØªØ¨Ø§Ø± Ø§Ù„Ø®Ø§Øµ Ø¨Ùƒ Ù‡Ùˆ: $exam_date.\n\nÙ…Ø¹ ØªØ­ÙŠØ§ØªÙ†Ø§,\nÙØ±ÙŠÙ‚ Ø§Ù„Ø¥Ø¯Ø§Ø±Ø©";
            $headers = "From: admin@example.com"; // ØªØ£ÙƒØ¯ Ù…Ù† ØªØ¹Ø¯ÙŠÙ„ Ø§Ù„Ø¨Ø±ÙŠØ¯ Ø§Ù„Ø¥Ù„ÙƒØªØ±ÙˆÙ†ÙŠ Ù‡Ù†Ø§

            mail($to, $subject, $message, $headers);
        }

        // Ø¥Ø¹Ø§Ø¯Ø© Ø§Ù„ØªÙˆØ¬ÙŠÙ‡ Ø¥Ù„Ù‰ Ù„ÙˆØ­Ø© Ø§Ù„ØªØ­ÙƒÙ… Ù…Ø¹ Ø±Ø³Ø§Ù„Ø© Ù†Ø¬Ø§Ø­
        header("Location: ../pages/admin_../pages/dashboard.php?message=ØªÙ… ØªØ­Ø¯ÙŠØ« Ù…ÙˆØ¹Ø¯ Ø§Ù„Ø§Ø®ØªØ¨Ø§Ø± Ø¨Ù†Ø¬Ø§Ø­");
        exit();
    } else {
        die("Ø®Ø·Ø£ ÙÙŠ ØªØ­Ø¯ÙŠØ« Ø§Ù„Ø·Ù„Ø¨: " . $conn->error);
    }
} else {
    header("Location: ../pages/admin_../pages/dashboard.php");
    exit();
}
?>

