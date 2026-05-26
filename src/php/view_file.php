<?php
session_start();
include '../includes/db_connection.php';

// ØªØ£ÙƒØ¯ Ù…Ù† Ø£Ù† Ø§Ù„Ù…Ø³ØªØ®Ø¯Ù… Ù…Ø³Ø¬Ù„ Ø¯Ø®ÙˆÙ„Ù‡ ÙƒÙ…Ø³Ø¤ÙˆÙ„
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die('ØºÙŠØ± Ù…ØµØ±Ø­ Ù„Ùƒ Ø¨Ø§Ù„ÙˆØµÙˆÙ„ Ø¥Ù„Ù‰ Ù‡Ø°Ù‡ Ø§Ù„ØµÙØ­Ø©.');
}

if (!isset($_GET['file']) || empty($_GET['file'])) {
    die('Ù„Ù… ÙŠØªÙ… ØªØ­Ø¯ÙŠØ¯ Ù…Ù„Ù.');
}

$file_path = $_GET['file'];

// Ø§Ù„ØªØ­Ù‚Ù‚ Ù…Ù† ÙˆØ¬ÙˆØ¯ Ø§Ù„Ù…Ù„Ù ÙˆØ£Ù†Ù‡ Ø¶Ù…Ù† Ø§Ù„Ù…Ø¬Ù„Ø¯ Ø§Ù„Ù…Ø³Ù…ÙˆØ­ Ø¨Ù‡
$allowed_dir = __DIR__ . '/../../uploads/'; // ØªØ£ÙƒØ¯ Ù…Ù† ØªØºÙŠÙŠØ± Ù‡Ø°Ø§ Ø¥Ù„Ù‰ Ø§Ù„Ù…Ø¬Ù„Ø¯ Ø§Ù„ØµØ­ÙŠØ­ Ø­ÙŠØ« ÙŠØªÙ… ØªØ®Ø²ÙŠÙ† Ø§Ù„Ù…Ù„ÙØ§Øª Ø§Ù„Ù…Ø±ÙÙ‚Ø©
$real_path = realpath($allowed_dir . basename($file_path));

if ($real_path === false || strpos($real_path, realpath($allowed_dir)) !== 0) {
    die('Ø§Ù„Ù…Ù„Ù ØºÙŠØ± Ù…ÙˆØ¬ÙˆØ¯ Ø£Ùˆ ØºÙŠØ± Ù…ØµØ±Ø­ Ø¨Ø§Ù„ÙˆØµÙˆÙ„ Ø¥Ù„ÙŠÙ‡.');
}

// Ø§Ù„Ø­ØµÙˆÙ„ Ø¹Ù„Ù‰ Ù†ÙˆØ¹ MIME Ù„Ù„Ù…Ù„Ù
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $real_path);
finfo_close($finfo);

// ØªØ­Ø¯ÙŠØ¯ ÙƒÙŠÙÙŠØ© Ø¹Ø±Ø¶ Ø§Ù„Ù…Ù„Ù Ø¨Ù†Ø§Ø¡Ù‹ Ø¹Ù„Ù‰ Ù†ÙˆØ¹Ù‡
switch ($mime_type) {
    case 'application/pdf':
        header('Content-Type: application/pdf');
        readfile($real_path);
        break;
    case 'image/jpeg':
    case 'image/png':
    case 'image/gif':
        header('Content-Type: ' . $mime_type);
        readfile($real_path);
        break;
    case 'text/plain':
        header('Content-Type: text/plain');
        readfile($real_path);
        break;
    default:
        // Ù„Ù„Ù…Ù„ÙØ§Øª Ø§Ù„Ø£Ø®Ø±Ù‰ØŒ Ù‚Ù… Ø¨ØªÙ†Ø²ÙŠÙ„Ù‡Ø§
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
        header('Content-Length: ' . filesize($real_path));
        readfile($real_path);
        break;
}

