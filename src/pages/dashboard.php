<?php
session_start();
include '../includes/db_connection.php';

// ØªØ£ÙƒØ¯ Ù…Ù† Ø£Ù† Ø§Ù„Ù…Ø³ØªØ®Ø¯Ù… Ù…Ø³Ø¬Ù„ Ø¯Ø®ÙˆÙ„Ù‡
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Ø¬Ù„Ø¨ Ø§Ù„Ø·Ù„Ø¨Ø§Øª Ø§Ù„ØªÙŠ ØªØ­ØªØ§Ø¬ Ø¥Ù„Ù‰ Ù…Ø±Ø§Ø¬Ø¹Ø©
$sql = "SELECT * FROM requests WHERE current_admin='" . $_SESSION['user_id'] . "'";
$result = $conn->query($sql);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $request_id = $_POST['request_id'];
    $action = $_POST['action'];

    if ($action == 'approve') {
        $sql = "SELECT * FROM requests WHERE id=$request_id";
        $request = $conn->query($sql)->fetch_assoc();
        
        $next_admin = $request['current_admin'] + 1;
        $sql = "UPDATE requests SET current_admin=$next_admin WHERE id=$request_id";
        $conn->query($sql);

        if ($next_admin > 4) {
            $sql = "UPDATE requests SET status='Approved' WHERE id=$request_id";
            $conn->query($sql);
        }
    } elseif ($action == 'reject') {
        $sql = "UPDATE requests SET status='Rejected' WHERE id=$request_id";
        $conn->query($sql);
    }

    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ù„ÙˆØ­Ø© Ø§Ù„ØªØ­ÙƒÙ… - Ø¥Ø¯Ø§Ø±Ø© Ø§Ù„Ø·Ù„Ø¨Ø§Øª</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <h2>Ø§Ù„Ø·Ù„Ø¨Ø§Øª Ø§Ù„Ù…Ø¹Ù„Ù‚Ø©</h2>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='request'>";
                echo "<h3>Ø·Ù„Ø¨ Ø±Ù‚Ù…: " . $row['id'] . "</h3>";
                echo "<p>" . $row['request_text'] . "</p>";
                echo "<form method='POST' action='dashboard.php'>";
                echo "<input type='hidden' name='request_id' value='" . $row['id'] . "'>";
                echo "<button type='submit' name='action' value='approve' class='approve-btn'>Ù…ÙˆØ§ÙÙ‚Ø©</button>";
                echo "<button type='submit' name='action' value='reject' class='reject-btn'>Ø±ÙØ¶</button>";
                echo "</form>";
                echo "</div>";
            }
        } else {
            echo "<p>Ù„Ø§ ØªÙˆØ¬Ø¯ Ø·Ù„Ø¨Ø§Øª Ù„Ù„Ù…Ø±Ø§Ø¬Ø¹Ø© ÙÙŠ Ø§Ù„ÙˆÙ‚Øª Ø§Ù„Ø­Ø§Ù„ÙŠ.</p>";
        }
        ?>
    </div>
</body>
</html>

