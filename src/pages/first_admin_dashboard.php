<?php
session_start();
include '../includes/db_connection.php';

// ØªØ­Ù‚Ù‚ Ù…Ù† ØªØ³Ø¬ÙŠÙ„ Ø¯Ø®ÙˆÙ„ Ø§Ù„Ù…Ø³ØªØ®Ø¯Ù…
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ø¬Ù„Ø¨ Ù…Ø¹Ø±Ù Ø§Ù„Ù…Ø³ØªØ®Ø¯Ù… Ø§Ù„Ø­Ø§Ù„ÙŠ
$current_admin_id = $_SESSION['user_id'];

// Ø¬Ù„Ø¨ Ù…Ø¹Ù„ÙˆÙ…Ø§Øª Ø§Ù„Ù…Ø³Ø¤ÙˆÙ„ Ø§Ù„Ø­Ø§Ù„ÙŠ
$sql_admin = "SELECT * FROM users WHERE id = ?";
$stmt_admin = $conn->prepare($sql_admin);
$stmt_admin->bind_param("i", $current_admin_id);
$stmt_admin->execute();
$result_admin = $stmt_admin->get_result();

if ($result_admin->num_rows > 0) {
    $current_admin = $result_admin->fetch_assoc();
    $current_admin_department = $current_admin['department_id'];
    $current_admin_role_id = $current_admin['role_id'];
} else {
    die("Admin not found.");
}

// Ø§Ù„ØªØ­Ù‚Ù‚ Ù…Ù† Ø£Ù† Ø§Ù„Ù…Ø³ØªØ®Ø¯Ù… Ù‡Ùˆ Ù…Ø³Ø¤ÙˆÙ„ Ø£ÙˆÙ„
if ($current_admin_role_id != 1) {
    echo "Access denied. User is not an admin.";
    exit();
}

// Ø¬Ù„Ø¨ Ø§Ù„Ø·Ù„Ø¨Ø§Øª Ø§Ù„Ù…Ø±ØªØ¨Ø·Ø© Ø¨Ù‡Ø°Ø§ Ø§Ù„Ù…Ø³Ø¤ÙˆÙ„ ÙˆØ§Ù„Ù‚Ø³Ù…
$sql_requests = "SELECT r.*, s.name AS student_name 
                 FROM requests r 
                 JOIN students s ON r.student_id = s.id 
                 WHERE r.current_admin = ?
                 AND r.status NOT IN ('Approved', 'Rejected')
                 AND s.department_id = ?";
$stmt_requests = $conn->prepare($sql_requests);
$stmt_requests->bind_param("ii", $current_admin_id, $current_admin_department);
$stmt_requests->execute();
$result_requests = $stmt_requests->get_result();

$requests = [];
if ($result_requests->num_rows > 0) {
    while ($request = $result_requests->fetch_assoc()) {
        $requests[] = $request;
    }
}

// Ù…Ø¹Ø§Ù„Ø¬Ø© Ø·Ù„Ø¨Ø§Øª Ø§Ù„Ù…ÙˆØ§ÙÙ‚Ø© Ø£Ùˆ Ø§Ù„Ø±ÙØ¶
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $request_id = $_POST['request_id'] ?? '';

    if ($action === 'approve') {
        // Ù†Ù‚Ù„ Ø§Ù„Ø·Ù„Ø¨ Ø¥Ù„Ù‰ Ø§Ù„Ù…Ø³Ø¤ÙˆÙ„ Ø§Ù„ØªØ§Ù„ÙŠ (role_id = 2)
        $next_admin_role_id = 2;

        // Ø¬Ù„Ø¨ Ø§Ù„Ù…Ø³Ø¤ÙˆÙ„ Ø§Ù„ØªØ§Ù„ÙŠ
        $sql_next_admin = "SELECT id FROM users WHERE role_id = ? LIMIT 1";
        $stmt_next_admin = $conn->prepare($sql_next_admin);
        $stmt_next_admin->bind_param("i", $next_admin_role_id);
        $stmt_next_admin->execute();
        $result_next_admin = $stmt_next_admin->get_result();

        if ($result_next_admin->num_rows > 0) {
            $next_admin = $result_next_admin->fetch_assoc();
            $next_admin_id = $next_admin['id'];

            // ØªØ­Ø¯ÙŠØ« Ø§Ù„Ø·Ù„Ø¨
            $sql_update_request = "UPDATE requests 
                                   SET current_admin = ?, 
                                       current_level = current_level + 1
                                   WHERE id = ?";
            $stmt_update_request = $conn->prepare($sql_update_request);
            $stmt_update_request->bind_param("ii", $next_admin_id, $request_id);
            $stmt_update_request->execute();

            echo json_encode(['success' => true, 'message' => 'ØªÙ…Øª Ø§Ù„Ù…ÙˆØ§ÙÙ‚Ø© Ø¹Ù„Ù‰ Ø§Ù„Ø·Ù„Ø¨ ÙˆØ¥Ø±Ø³Ø§Ù„Ù‡ Ø¥Ù„Ù‰ Ø§Ù„Ù…Ø³Ø¤ÙˆÙ„ Ø§Ù„ØªØ§Ù„ÙŠ.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Ù„Ù… ÙŠØªÙ… Ø§Ù„Ø¹Ø«ÙˆØ± Ø¹Ù„Ù‰ Ø§Ù„Ù…Ø³Ø¤ÙˆÙ„ Ø§Ù„ØªØ§Ù„ÙŠ.']);
        }
    } elseif ($action === 'reject') {
        // Ø±ÙØ¶ Ø§Ù„Ø·Ù„Ø¨
        $rejection_reason = $_POST['rejection_reason'] ?? '';
        $sql_update_request = "UPDATE requests 
                               SET status = 'Rejected', 
                                   rejection_reason = ?, 
                                   processed_by = ? 
                               WHERE id = ?";
        $stmt_update_request = $conn->prepare($sql_update_request);
        $stmt_update_request->bind_param("sii", $rejection_reason, $current_admin_id, $request_id);
        $stmt_update_request->execute();

        echo json_encode(['success' => true, 'message' => 'ØªÙ… Ø±ÙØ¶ Ø§Ù„Ø·Ù„Ø¨.']);
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>Ù„ÙˆØ­Ø© Ø§Ù„Ù…Ø³Ø¤ÙˆÙ„ - Ø¥Ø¯Ø§Ø±Ø© Ø§Ù„Ø·Ù„Ø¨Ø§Øª</title>
    <!-- ØªÙ†Ø³ÙŠÙ‚Ø§Øª CSS ÙˆÙ…ÙƒØªØ¨Ø§Øª JavaScript Ù‡Ù†Ø§ -->
</head>
<body>
    <div class="header">
        <div class="logo">Ø¨ÙÙ†ÙŠØ©</div>
        <div class="nav">
            <a href="../php/logout.php">ØªØ³Ø¬ÙŠÙ„ Ø§Ù„Ø®Ø±ÙˆØ¬</a>
        </div>
    </div>
    <div class="container">
        <h2>Ù„ÙˆØ­Ø© Ø§Ù„Ù…Ø³Ø¤ÙˆÙ„ - Ø¥Ø¯Ø§Ø±Ø© Ø§Ù„Ø·Ù„Ø¨Ø§Øª</h2>
        <?php if (!empty($requests)): ?>
            <?php foreach ($requests as $request): ?>
                <div class="request" id="request-<?= $request['id'] ?>">
                    <div class="request-info">
                        <div class="admin-name">Ø§Ù„Ø·Ø§Ù„Ø¨: <?= htmlspecialchars($request['student_name']) ?></div>
                        <div>ØªØ§Ø±ÙŠØ® Ø§Ù„Ø·Ù„Ø¨: <?= htmlspecialchars($request['date']) ?></div>
                    </div>
                    <div>Ø§Ù„ÙˆØµÙ: <?= htmlspecialchars($request['description']) ?></div>
                    <div class="btn-group">
                        <button class="btn btn-accept" onclick="handleApproval(<?= $request['id'] ?>)">Ù‚Ø¨ÙˆÙ„</button>
                        <button class="btn btn-reject" onclick="handleRejection(<?= $request['id'] ?>)">Ø±ÙØ¶</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style='color: black; text-align: center;'>Ù„Ø§ ØªÙˆØ¬Ø¯ Ø·Ù„Ø¨Ø§Øª Ù„Ù„Ù…Ø±Ø§Ø¬Ø¹Ø© ÙÙŠ Ø§Ù„ÙˆÙ‚Øª Ø§Ù„Ø­Ø§Ù„ÙŠ.</p>
        <?php endif; ?>
    </div>

    <!-- JavaScript Ù„Ù…Ø¹Ø§Ù„Ø¬Ø© Ø§Ù„Ø£Ø­Ø¯Ø§Ø« -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        window.handleApproval = function(requestId) {
            $.ajax({
                url: 'first_admin_dashboard.php',
                type: 'POST',
                data: { action: 'approve', request_id: requestId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Ù†Ø¬Ø§Ø­',
                            text: response.message,
                        });
                        removeRequest(requestId);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Ø®Ø·Ø£',
                            text: response.message,
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error handling approval:", error);
                }
            });
        }

        window.handleRejection = function(requestId) {
            Swal.fire({
                title: 'Ø§ÙƒØªØ¨ Ø³Ø¨Ø¨ Ø§Ù„Ø±ÙØ¶',
                input: 'textarea',
                inputPlaceholder: 'Ø§Ø¯Ø®Ù„ Ø³Ø¨Ø¨ Ø§Ù„Ø±ÙØ¶ Ù‡Ù†Ø§...',
                showCancelButton: true,
                confirmButtonText: 'Ø±ÙØ¶ Ø§Ù„Ø·Ù„Ø¨',
                cancelButtonText: 'Ø¥Ù„ØºØ§Ø¡',
                preConfirm: (rejectionReason) => {
                    if (!rejectionReason) {
                        Swal.showValidationMessage('ÙŠØ±Ø¬Ù‰ Ø¥Ø¯Ø®Ø§Ù„ Ø³Ø¨Ø¨ Ø§Ù„Ø±ÙØ¶');
                    }
                    return rejectionReason;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const rejectionReason = result.value;
                    $.ajax({
                        url: 'first_admin_dashboard.php',
                        type: 'POST',
                        data: {
                            action: 'reject',
                            request_id: requestId,
                            rejection_reason: rejectionReason
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Ù†Ø¬Ø§Ø­',
                                    text: response.message,
                                });
                                removeRequest(requestId);
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Ø®Ø·Ø£',
                                    text: response.message,
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("Error handling rejection:", error);
                        }
                    });
                }
            });
        }

        function removeRequest(requestId) {
            $('#request-' + requestId).fadeOut(400, function() {
                $(this).remove();
                if ($('.request').length === 0) {
                    $('.container').html("<p style='color: black; text-align: center;'>Ù„Ø§ ØªÙˆØ¬Ø¯ Ø·Ù„Ø¨Ø§Øª Ù„Ù„Ù…Ø±Ø§Ø¬Ø¹Ø© ÙÙŠ Ø§Ù„ÙˆÙ‚Øª Ø§Ù„Ø­Ø§Ù„ÙŠ.</p>");
                }
            });
        }
    });
    </script>
</body>
</html>

