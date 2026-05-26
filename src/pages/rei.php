<?php
session_start();
include '../includes/db_connection.php';

$message = '';
$idError = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $full_name = $_POST['name'];
    $email = $_POST['email'];
    $role = 'professor';

    // ØªØ­Ù‚Ù‚ Ù…Ù† Ø¹Ø¯Ù… ÙˆØ¬ÙˆØ¯ Ø­Ù‚ÙˆÙ„ ÙØ§Ø±ØºØ©
    if (empty($full_name)) {
        $message = "Ø§Ù„Ø§Ø³Ù… Ø§Ù„ÙƒØ§Ù…Ù„ Ù„Ø§ ÙŠÙ…ÙƒÙ† Ø£Ù† ÙŠÙƒÙˆÙ† ÙØ§Ø±ØºØ§Ù‹";
    } else {
        // Ø§Ù„ØªØ­Ù‚Ù‚ Ù…Ù† Ø¹Ø¯Ù… ÙˆØ¬ÙˆØ¯ Ù†ÙØ³ Ø§Ù„Ù‚ÙŠÙ…Ø© Ù„Ù„Ù…ÙØªØ§Ø­ Ø§Ù„Ø±Ø¦ÙŠØ³ÙŠ Ø¨Ø§Ù„ÙØ¹Ù„
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
        $check_stmt->bind_param("s", $id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            $idError = "Ù‡Ø°Ø§ Ø§Ù„Ù…Ø¹Ø±Ù Ù…ÙˆØ¬ÙˆØ¯ Ø¨Ø§Ù„ÙØ¹Ù„ØŒ ÙŠØ±Ø¬Ù‰ Ø§Ø³ØªØ®Ø¯Ø§Ù… Ù…Ø¹Ø±Ù Ø¢Ø®Ø±.";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (id, password, name, email, role) VALUES (?, ?, ?, ?, ?)");
            if (!$stmt) {
                die("Error in preparing statement: " . $conn->error);
            }

            $stmt->bind_param("sssss", $id, $password, $full_name, $email, $role);

            if ($stmt->execute()) {
                $message = "ØªÙ… Ø¥Ù†Ø´Ø§Ø¡ Ø­Ø³Ø§Ø¨ Ø§Ù„Ø¯ÙƒØªÙˆØ± Ø¨Ù†Ø¬Ø§Ø­";
            } else {
                $message = "Ø­Ø¯Ø« Ø®Ø·Ø£: " . $stmt->error;
            }

            $stmt->close();
        }

        $check_stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ØªØ³Ø¬ÙŠÙ„ Ø­Ø³Ø§Ø¨ Ø¯ÙƒØªÙˆØ± Ø¬Ø¯ÙŠØ¯</title>
    <style>
       
    </style>
</head>
<body>
<form action="rei.php" method="post">
        <h2>ØªØ³Ø¬ÙŠÙ„ Ø­Ø³Ø§Ø¨ Ø¯ÙƒØªÙˆØ± Ø¬Ø¯ÙŠØ¯</h2>
        <input type="text" name="id" placeholder="ID" required>
        <input type="password" name="password" placeholder="ÙƒÙ„Ù…Ø© Ø§Ù„Ù…Ø±ÙˆØ±" required>
        <input type="text" name="name" placeholder="Ø§Ù„Ø§Ø³Ù… Ø§Ù„ÙƒØ§Ù…Ù„" required>
        <input type="email" name="email" placeholder="Ø§Ù„Ø¨Ø±ÙŠØ¯ Ø§Ù„Ø¥Ù„ÙƒØªØ±ÙˆÙ†ÙŠ" required>
        <input type="submit" value="ØªØ³Ø¬ÙŠÙ„">
    </form>

    <!-- Ø¹Ø±Ø¶ Ø±Ø³Ø§Ù„Ø© Ø§Ù„Ø®Ø·Ø£ Ø§Ù„Ø®Ø§ØµØ© Ø¨Ø§Ù„Ù…Ø¹Ø±Ù ÙÙ‚Ø· Ø¨Ø¹Ø¯ Ø¥Ø±Ø³Ø§Ù„ Ø§Ù„Ù†Ù…ÙˆØ°Ø¬ -->
    <?php if (!empty($idError)): ?>
        <div class="error"><?php echo $idError; ?></div>
    <?php endif; ?>

    <!-- Ø¹Ø±Ø¶ Ø§Ù„Ø±Ø³Ø§Ù„Ø© Ø§Ù„Ø£Ø®Ø±Ù‰ ÙÙ‚Ø· Ø¨Ø¹Ø¯ Ø¥Ø¯Ø®Ø§Ù„ Ø§Ù„Ø¨ÙŠØ§Ù†Ø§Øª -->
    <?php if (!empty($message)): ?>
        <div class="message <?php echo ($message === "ØªÙ… Ø¥Ù†Ø´Ø§Ø¡ Ø­Ø³Ø§Ø¨ Ø§Ù„Ø¯ÙƒØªÙˆØ± Ø¨Ù†Ø¬Ø§Ø­") ? 'success' : 'error'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
</body>
</html>
