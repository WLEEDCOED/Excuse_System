<?php
include '../includes/db_connection.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_../pages/login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $review_id = $_POST['review_id'];
    $excuse_id = $_POST['excuse_id'];
    $action = $_POST['action'];

    if ($action == 'approve') {
        // Update the current review
        $sql = "UPDATE reviews SET status = 'approved' WHERE id = ? AND admin_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $review_id, $admin_id);
        $stmt->execute();

        // Check if there's a next admin
        $next_admin_id = $admin_id + 1;
        $sql = "SELECT id FROM users WHERE id = ? AND role = 'admin'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $next_admin_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Assign to next admin
            $sql = "INSERT INTO reviews (excuse_id, admin_id) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $excuse_id, $next_admin_id);
            $stmt->execute();
        } else {
            // All admins have approved, update excuse status
            $sql = "UPDATE excuses SET status = 'approved' WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $excuse_id);
            $stmt->execute();
        }
    } elseif ($action == 'reject') {
        // Update review and excuse status to rejected
        $sql = "UPDATE reviews SET status = 'rejected' WHERE id = ? AND admin_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $review_id, $admin_id);
        $stmt->execute();

        $sql = "UPDATE excuses SET status = 'rejected' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $excuse_id);
        $stmt->execute();
    }

    header("Location: ../pages/admin_../pages/dashboard.php");
    exit();
}
?>
