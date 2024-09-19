<?php
include 'db_connection.php';
session_start();

if ($_SESSION['role'] !== 'admin1' && $_SESSION['role'] !== 'admin2' && $_SESSION['role'] !== 'admin3' && $_SESSION['role'] !== 'admin4') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $request_id = $_POST['request_id'];
    $action = $_POST['action'];
    $current_level = intval(substr($_SESSION['role'], -1));

    if ($action === 'approve') {
        if ($current_level < 4) {
            $next_level = $current_level + 1;
            $sql = "UPDATE requests SET current_level = ?, processed_by = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iii", $next_level, $_SESSION['user_id'], $request_id);
        } else {
            $sql = "UPDATE requests SET status = 'Approved', processed_by = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $_SESSION['user_id'], $request_id);
        }
    } else {
        $sql = "UPDATE requests SET status = 'Rejected', processed_by = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $_SESSION['user_id'], $request_id);
    }

    $stmt->execute();
    header("Location: admin_dashboard.php");
    exit();
}
?>
