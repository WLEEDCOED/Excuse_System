<?php
session_start();
include '../includes/db_connection.php'; // ØªØ£ÙƒØ¯ Ù…Ù† ØªØ¶Ù…ÙŠÙ† Ù…Ù„Ù Ø§Ù„Ø§ØªØµØ§Ù„ Ø¨Ù‚Ø§Ø¹Ø¯Ø© Ø§Ù„Ø¨ÙŠØ§Ù†Ø§Øª

// Ø¬Ù„Ø¨ Ø§Ø³Ù… Ø§Ù„Ù…Ø³Ø¤ÙˆÙ„ Ø§Ù„Ø­Ø§Ù„ÙŠ Ù…Ù† Ù‚Ø§Ø¹Ø¯Ø© Ø§Ù„Ø¨ÙŠØ§Ù†Ø§Øª
$current_admin_id = $_SESSION['user_id'];
$sql_admin_name = "SELECT name FROM admins WHERE id = $current_admin_id";
$result_admin_name = $conn->query($sql_admin_name);

if ($result_admin_name && $result_admin_name->num_rows > 0) {
    $admin_row = $result_admin_name->fetch_assoc();
    $current_admin_name = $admin_row['name'];
} else {
    $current_admin_name = "Unknown Admin"; // Ù‚ÙŠÙ…Ø© Ø§ÙØªØ±Ø§Ø¶ÙŠØ© Ø¥Ø°Ø§ Ù„Ù… ÙŠØªÙ… Ø§Ù„Ø¹Ø«ÙˆØ± Ø¹Ù„Ù‰ Ø§Ù„Ø§Ø³Ù…
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $request_id = $_POST['request_id'] ?? '';
    $current_admin_id = $_SESSION['user_id'];

    if ($action == 'approve') {
        // Ø¬Ù„Ø¨ Ø¬Ù…ÙŠØ¹ Ø§Ù„Ù…Ø³Ø¤ÙˆÙ„ÙŠÙ† ÙˆØªØ±ØªÙŠØ¨Ù‡Ù… Ø­Ø³Ø¨ ID
        $sql_admins = "SELECT id FROM admins ORDER BY id ASC";
        $admins_result = $conn->query($sql_admins);
        if (!$admins_result) {
            die("Error executing query: " . $conn->error);
        }

        $next_admin_id = null;
        $found = false;

        // Ø¥ÙŠØ¬Ø§Ø¯ Ø§Ù„Ù…Ø³Ø¤ÙˆÙ„ Ø§Ù„ØªØ§Ù„ÙŠ
        while ($admin = $admins_result->fetch_assoc()) {
            if ($found) {
                $next_admin_id = $admin['id'];
                break;
            }
            if ($admin['id'] == $current_admin_id) {
                $found = true;
            }
        }

        // ØªØ­Ø¯ÙŠØ« Ø§Ù„Ø·Ù„Ø¨ Ù„ÙŠØ±Ø³Ù„Ù‡ Ù„Ù„Ù…Ø³Ø¤ÙˆÙ„ Ø§Ù„ØªØ§Ù„ÙŠ
        if ($next_admin_id) {
            $sql = "UPDATE requests SET current_admin = $next_admin_id, current_level = current_level + 1 WHERE id = $request_id";
            $conn->query($sql);
            echo json_encode(['showDatePicker' => false]);
        } else {
            // Ø¥Ø°Ø§ ÙƒØ§Ù† Ù„Ø§ ÙŠÙˆØ¬Ø¯ Ù…Ø³Ø¤ÙˆÙ„ Ø¢Ø®Ø±ØŒ ÙŠØªÙ… ØªØºÙŠÙŠØ± Ø­Ø§Ù„Ø© Ø§Ù„Ø·Ù„Ø¨ Ø¥Ù„Ù‰ "Approved"
            $sql = "UPDATE requests SET status = 'Approved', processed_by = $current_admin_id WHERE id = $request_id";
            $conn->query($sql);
            echo json_encode(['showDatePicker' => true, 'requestId' => $request_id]);
        }

        exit();
    } elseif ($action == 'reject') {
        // ØªØ­Ø¯ÙŠØ« Ø­Ø§Ù„Ø© Ø§Ù„Ø·Ù„Ø¨ Ø¥Ù„Ù‰ "Rejected" ÙˆØ¥Ø¶Ø§ÙØ© Ø³Ø¨Ø¨ Ø§Ù„Ø±ÙØ¶
        $rejection_reason = $conn->real_escape_string($_POST['rejection_reason']); // ØªØ£ÙƒØ¯ Ù…Ù† Ø§Ù„ØªØ¹Ø§Ù…Ù„ Ù…Ø¹ Ø§Ù„Ø³Ø¨Ø¨ Ø¨Ø´ÙƒÙ„ Ø¢Ù…Ù†
        $sql = "UPDATE requests SET status = 'Rejected', rejection_reason = '$rejection_reason', processed_by = $current_admin_id WHERE id = $request_id";
        $conn->query($sql);

        echo json_encode(['showDatePicker' => false]);
        exit();
    } elseif ($action == 'set_exam_date') {
        $exam_date = $_POST['exam_date'];

        // ØªØ­Ø¯ÙŠØ« Ø§Ù„Ø·Ù„Ø¨ Ø¨ØªØ§Ø±ÙŠØ® Ø§Ù„Ø§Ù…ØªØ­Ø§Ù†
        $sql_update_exam_date = "UPDATE requests SET exam_date = ?, status = 'Approved' WHERE id = ?";
        $stmt_update_exam_date = $conn->prepare($sql_update_exam_date);
        $stmt_update_exam_date->bind_param("si", $exam_date, $request_id);
        if (!$stmt_update_exam_date->execute()) {
            error_log("Execution error: " . $stmt_update_exam_date->error);
        }

        echo json_encode(['showDatePicker' => false]);
        exit();
    } elseif ($action == 'send_to_council') {
        // Ø¥Ø±Ø³Ø§Ù„ Ø§Ù„Ø·Ù„Ø¨ Ø¥Ù„Ù‰ Ù…Ø¬Ù„Ø³ Ø§Ù„ÙƒÙ„ÙŠØ©
        $sql = "UPDATE requests SET status = 'Sent to Council', processed_by = $current_admin_id WHERE id = $request_id";
        if ($conn->query($sql) === true) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        exit();
    }
}

// Ø§Ø³ØªØ¹Ù„Ø§Ù… Ù„Ø¬Ù„Ø¨ Ø§Ù„Ø·Ù„Ø¨Ø§Øª

                 $sql_requests = "SELECT requests.*, students.name AS student_name 
                 FROM requests 
                 JOIN students ON requests.student_id = students.id 
                 WHERE (requests.status = 'Pending' OR requests.status = 'Sent to Council')
                 AND requests.current_admin = $current_admin_id";

$result = $conn->query($sql_requests);
if (!$result) {
    die("Error executing query: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ù„ÙˆØ­Ø© Ø§Ù„Ù…Ø³Ø¤ÙˆÙ„ - Ø¥Ø¯Ø§Ø±Ø© Ø§Ù„Ø·Ù„Ø¨Ø§Øª</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
        }

        .header {
            background-color: #00a86b;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #fff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header .logo {
            font-size: 24px;
            font-weight: bold;
        }

        .header .nav a {
            margin-left: 20px;
            text-decoration: none;
            color: #fff;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        .header .nav a:hover {
            color: #e0f5f0;
        }
        .send-to-council-box {
    background-color: #f8f9fa;
    padding: 10px;
    border-radius: 5px;
    margin-top: 10px;
}


        .container {
    max-width: 900px;
    margin: 40px auto;
    padding: 20px;
    background-color: #fff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
    display: flex;
    flex-direction: column; /* Ù„ØªØ¸Ù‡Ø± Ø§Ù„Ø·Ù„Ø¨Ø§Øª ØªØ­Øª Ø¨Ø¹Ø¶Ù‡Ø§ */
    gap: 20px; /* Ù…Ø³Ø§ÙØ© Ø¨ÙŠÙ† ÙƒÙ„ Ø·Ù„Ø¨ ÙˆØ¢Ø®Ø± */
}

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }

        .request {
    background-color: #00a86b;
    border-radius: 10px;
    padding: 20px;
    color: #fff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column; /* Ø§Ù„ØªØ£ÙƒØ¯ Ù…Ù† Ø£Ù† Ø§Ù„Ù…Ø­ØªÙˆÙŠØ§Øª Ø¯Ø§Ø®Ù„ Ø§Ù„Ø·Ù„Ø¨ Ù…Ù†Ø¸Ù…Ø© Ø¹Ù…ÙˆØ¯ÙŠØ§Ù‹ */
    gap: 10px; /* Ø¥Ø¶Ø§ÙØ© Ù…Ø³Ø§ÙØ© Ø¨ÙŠÙ† Ø§Ù„Ø¹Ù†Ø§ØµØ± Ø¯Ø§Ø®Ù„ Ø§Ù„Ø·Ù„Ø¨ */
}

.request-info {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px; /* Ø¥Ø¶Ø§ÙØ© Ù…Ø³Ø§ÙØ© Ø¨ÙŠÙ† Ø§Ù„Ø¹Ù†Ø§ØµØ± Ø¯Ø§Ø®Ù„ request-info */
}

        .admin-name {
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 10px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s ease, color 0.3s ease;
            margin: 5px;
        }

        .btn-accept {
            background-color: #fff;
            color: #00a86b;
        }

        .btn-reject {
            background-color: #fff;
            color: #d9534f;
        }

        .btn:hover {
            background-color: #00a86b;
            color: #fff;
        }

        .btn-reject:hover {
            background-color: #d9534f;
            color: #fff;
        }

        .btn-display {
            background-color: #fff;
            color: #00a86b;
        }

        .btn-display:hover {
            background-color: #00a86b;
            color: #fff;
        }

        .details {
            display: none;
            background-color: #fff;
            color: #333;
            padding: 15px;
            border-radius: 10px;
            margin-top: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .show-details {
            display: block;
        }

        .success-message {
            display: none;
            background-color: #28a745;
            color: #fff;
            padding: 10px;
            border-radius: 10px;
            text-align: center;
            margin-top: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .success-show {
            display: block;
        }

        #datePicker {
            display: none;
        }
  

    </style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            function loadRequests() {
                $.ajax({
                    url: '../php/get_requests.php',
                    type: 'GET',
                    success: function(data) {
                        if (data.trim() === '') {
                            $('.container').html(`<p style='color: black; text-align: center;'>Ù„Ø§ ØªÙˆØ¬Ø¯ Ø·Ù„Ø¨Ø§Øª Ù„Ù„Ù…Ø±Ø§Ø¬Ø¹Ø© ÙÙŠ Ø§Ù„ÙˆÙ‚Øª Ø§Ù„Ø­Ø§Ù„ÙŠ.</p>`);
                        } else {
                            $('.container').html(data);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error loading requests:", error);
                    }
                });
            }

            loadRequests();
            setInterval(loadRequests, 30000);

            window.handleApproval = function(requestId) {
                $.ajax({
                    url: 'admin_dashboard.php',
                    type: 'POST',
                    data: { action: 'approve', request_id: requestId },
                    dataType: 'json',
                    success: function(data) {
                        if (data.showDatePicker) {
                            setExamDate(requestId);
                        } else {
                            removeRequest(requestId);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error approving request:", error);
                    }
                });
            };

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
                            url: 'admin_dashboard.php',
                            type: 'POST',
                            data: {
                                action: 'reject',
                                request_id: requestId,
                                rejection_reason: rejectionReason
                            },
                            dataType: 'json',
                            success: function(data) {
                                removeRequest(requestId);
                                showSuccess();
                            },
                            error: function(xhr, status, error) {
                                console.error("Error rejecting request:", error);
                            }
                        });
                    }
                });
            };

        
            window.setExamDate = function(requestId) {
    Swal.fire({
        title: 'Ø­Ø¯Ø¯ ØªØ§Ø±ÙŠØ® Ø§Ù„Ø§Ù…ØªØ­Ø§Ù†',
        html: '<input type="date" id="examDatePicker" class="swal2-input">',
        showCancelButton: true,
        confirmButtonText: 'ØªØ£ÙƒÙŠØ¯',
        cancelButtonText: 'Ø¥Ù„ØºØ§Ø¡',
        preConfirm: () => {
            const examDate = $('#examDatePicker').val();
            if (!examDate) {
                Swal.showValidationMessage('ÙŠØ±Ø¬Ù‰ Ø§Ø®ØªÙŠØ§Ø± ØªØ§Ø±ÙŠØ® Ø§Ù„Ø§Ù…ØªØ­Ø§Ù†');
            }
            return examDate;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const examDate = result.value;
            $.ajax({
                url: 'admin_dashboard.php',
                type: 'POST',
                data: {
                    action: 'set_exam_date',
                    request_id: requestId,
                    exam_date: examDate
                },
                dataType: 'json',
                success: function(response) {
                    // ØªØ­Ø¯ÙŠØ« Ù‚Ø§Ø¹Ø¯Ø© Ø§Ù„Ø¨ÙŠØ§Ù†Ø§Øª
                    removeRequest(requestId);  // Ø¥Ø²Ø§Ù„Ø© Ø§Ù„Ø·Ù„Ø¨ Ø¨Ø¹Ø¯ Ø§Ù„ØªØ­Ø¯ÙŠØ« Ø§Ù„Ù†Ø§Ø¬Ø­
                    showSuccess();  // Ø¹Ø±Ø¶ Ø±Ø³Ø§Ù„Ø© Ù†Ø¬Ø§Ø­
                },
                error: function(xhr, status, error) {
                    console.error("Error setting exam date:", error);
                }
            });
        }
    });
};

function removeRequest(requestId) {
    const statusText = $('#request-' + requestId).find('.status').text();
    if (statusText !== 'Ù‚ÙŠØ¯ Ø§Ù„Ù…Ø±Ø§Ø¬Ø¹Ø© Ù…Ù† Ù…Ø¬Ù„Ø³ Ø§Ù„ÙƒÙ„ÙŠØ©') {
        $('#request-' + requestId).fadeOut(400, function() {
            $(this).remove();
            if ($('.request').length === 0) {
                $('.container').html("<p style='color: black; text-align: center;'>Ù„Ø§ ØªÙˆØ¬Ø¯ Ø·Ù„Ø¨Ø§Øª Ù„Ù„Ù…Ø±Ø§Ø¬Ø¹Ø© ÙÙŠ Ø§Ù„ÙˆÙ‚Øª Ø§Ù„Ø­Ø§Ù„ÙŠ.</p>");
            }
        });
    }
}

     
            window.showSuccess = function() {
                Swal.fire({
                    icon: 'success',
                    title: 'ØªÙ… ØªØ­Ø¯ÙŠØ« Ø§Ù„Ø·Ù„Ø¨ Ø¨Ù†Ø¬Ø§Ø­',
                    showConfirmButton: false,
                    timer: 1500
                });
            };
        });
        window.handleSendToCouncil = function(requestId) {
    Swal.fire({
        title: 'ØªØ£ÙƒÙŠØ¯ Ø§Ù„Ø¥Ø±Ø³Ø§Ù„ Ø¥Ù„Ù‰ Ù…Ø¬Ù„Ø³ Ø§Ù„ÙƒÙ„ÙŠØ©',
        text: "Ù‡Ù„ Ø£Ù†Øª Ù…ØªØ£ÙƒØ¯ Ø£Ù†Ùƒ ØªØ±ÙŠØ¯ Ø¥Ø±Ø³Ø§Ù„ Ù‡Ø°Ø§ Ø§Ù„Ø·Ù„Ø¨ Ø¥Ù„Ù‰ Ù…Ø¬Ù„Ø³ Ø§Ù„ÙƒÙ„ÙŠØ©ØŸ",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ù†Ø¹Ù…ØŒ Ø£Ø±Ø³Ù„',
        cancelButtonText: 'Ù„Ø§ØŒ Ø£Ù„ØºÙ'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'admin_dashboard.php',
                type: 'POST',
                data: { action: 'send_to_council', request_id: requestId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'ØªÙ… Ø¥Ø±Ø³Ø§Ù„ Ø§Ù„Ø·Ù„Ø¨ Ø¥Ù„Ù‰ Ù…Ø¬Ù„Ø³ Ø§Ù„ÙƒÙ„ÙŠØ© Ø¨Ù†Ø¬Ø§Ø­',
                            text: 'ØªÙ… ØªØ­Ø¯ÙŠØ« Ø­Ø§Ù„Ø© Ø§Ù„Ø·Ù„Ø¨.',
                            confirmButtonText: 'Ø­Ø³Ù†Ù‹Ø§'
                        });

                        // ØªØ­Ø¯ÙŠØ« Ø­Ø§Ù„Ø© Ø§Ù„Ø·Ù„Ø¨ ÙÙŠ ÙˆØ§Ø¬Ù‡Ø© Ø§Ù„Ù…Ø³ØªØ®Ø¯Ù… Ø¨Ø¯Ù„Ø§Ù‹ Ù…Ù† Ø¥Ø®ÙØ§Ø¦Ù‡
                        $('#request-' + requestId).find('.status').text('Ù‚ÙŠØ¯ Ø§Ù„Ù…Ø±Ø§Ø¬Ø¹Ø© Ù…Ù† Ù‚Ø¨Ù„ Ù…Ø¬Ù„Ø³ Ø§Ù„ÙƒÙ„ÙŠØ©');
                    } else {
                        Swal.fire('Ø­Ø¯Ø« Ø®Ø·Ø£: ' + response.error);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error sending to council:", error);
                    Swal.fire('Ø­Ø¯Ø« Ø®Ø·Ø£ Ø£Ø«Ù†Ø§Ø¡ Ø¥Ø±Ø³Ø§Ù„ Ø§Ù„Ø·Ù„Ø¨');
                }
            });
        }
    });
};

window.toggleDetails = function(id) {
            $('#details-' + id).toggleClass('show-details');
        };



    </script>
<body>
    <header class="header">
        <?php echo "<div class='admin-name'> Ù…Ø±Ø­Ø¨Ø§: " . htmlspecialchars($current_admin_name) . "</div>"; ?>
        <div class="nav">
            <a href="../php/logout.php">ØªØ³Ø¬ÙŠÙ„ Ø§Ù„Ø®Ø±ÙˆØ¬</a>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        </div>
    </header>

    <div class="container">
        <h2>Ø¥Ø¯Ø§Ø±Ø© Ø§Ù„Ø·Ù„Ø¨Ø§Øª</h2>
<?php

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<div id='request-" . $row['id'] . "' class='request'>";
        echo "<div class='request-info'>";
        echo "<span><strong>Ø§Ø³Ù… Ø§Ù„Ø·Ø§Ù„Ø¨:</strong> " . htmlspecialchars($row['student_name']) . "</span>";
        echo "<span><strong>Ø±Ù‚Ù… Ø§Ù„Ø¬Ø§Ù…Ø¹ÙŠ:</strong> " . htmlspecialchars($row['student_id']) . "</span>";
        echo "<span><strong>Ø±Ù‚Ù… Ø§Ù„Ø·Ù„Ø¨:</strong> " . htmlspecialchars($row['id']) . "</span>";
        echo "<span><strong>ØªØ§Ø±ÙŠØ® Ø§Ù„Ø·Ù„Ø¨:</strong> " . htmlspecialchars($row['date']) . "</span>";
        echo "<button class='btn btn-display' onclick='toggleDetails(" . $row['id'] . ")'>Ø¹Ø±Ø¶ Ø§Ù„Ø¹Ø°Ø±</button>";
        echo "</div>";

        echo "<div id='details-" . $row['id'] . "' class='details'>";
        echo "<p><strong>Ø§Ù„Ù…Ø§Ø¯Ø©:</strong> " . htmlspecialchars($row['class']) . "</p>";
        echo "<p><strong>Ø§Ù„ÙˆØµÙ:</strong> " . htmlspecialchars($row['description']) . "</p>";
        echo "<p><strong>Ø§Ù„Ø¹Ø°Ø±:</strong> " . htmlspecialchars($row['excuse']) . "</p>";
        echo "<p><strong>ØªØ§Ø±ÙŠØ® Ø§Ù„Ø·Ù„Ø¨:</strong> " . htmlspecialchars($row['date']) . "</p>";

        if ($row['file_path']) {
            echo "<p><strong>Ù…Ù„Ù Ù…Ø±ÙÙ‚:</strong> <a href='../php/view_file.php?file=" . urlencode($row['file_path']) . "' target='_blank'>Ø¹Ø±Ø¶ Ø§Ù„Ù…Ù„Ù</a></p>";
        }

        echo "<div class='actions'>";

        // Ø¹Ø±Ø¶ Ø£Ø²Ø±Ø§Ø± Ø§Ù„Ù‚Ø¨ÙˆÙ„ ÙˆØ§Ù„Ø±ÙØ¶ Ù„Ø¬Ù…ÙŠØ¹ Ø§Ù„Ù…Ø³Ø¤ÙˆÙ„ÙŠÙ† Ø¨Ø§Ø³ØªØ«Ù†Ø§Ø¡ Ø§Ù„Ù…Ø³Ø¤ÙˆÙ„ Ø§Ù„Ø«Ø§Ù„Ø«
        if ($current_admin_id != 3) {
            echo "<button type='button' class='btn btn-accept' onclick='handleApproval(" . $row['id'] . ")'>Ù‚Ø¨ÙˆÙ„</button>";
            echo "<button type='button' class='btn btn-reject' onclick='handleRejection(" . $row['id'] . ")'>Ø±ÙØ¶</button>";
        }

        // Ø¹Ø±Ø¶ Ø²Ø± "Ø¥Ø±Ø³Ø§Ù„ Ø¥Ù„Ù‰ Ù…Ø¬Ù„Ø³ Ø§Ù„ÙƒÙ„ÙŠØ©" ÙÙ‚Ø· Ù„Ù„Ù…Ø³Ø¤ÙˆÙ„ Ø§Ù„Ø«Ø§Ù†ÙŠ (ÙÙŠ Ø¬Ø¯ÙˆÙ„ admins) Ø¥Ø°Ø§ ÙƒØ§Ù† id = 2
        if ($current_admin_table == 'admins' && $current_admin_id == 2) {
            echo "<div class='send-to-council-box'>";
            echo "<button class='btn btn-send-to-council' onclick='handleSendToCouncil(" . $row['id'] . ")'>Ø¥Ø±Ø³Ø§Ù„ Ø¥Ù„Ù‰ Ù…Ø¬Ù„Ø³ Ø§Ù„ÙƒÙ„ÙŠØ©</button>";
            echo "</div>";
        }

        // Ø¹Ø±Ø¶ Ø²Ø± "ØªØ­Ø¯ÙŠØ¯ ØªØ§Ø±ÙŠØ® Ø§Ù„Ø§Ù…ØªØ­Ø§Ù†" ÙÙ‚Ø· Ù„Ù„Ù…Ø³Ø¤ÙˆÙ„ Ø§Ù„Ø«Ø§Ù„Ø«
        if ($current_admin_id == 3) {
            echo "<button class='btn btn-accept' onclick='setExamDate(" . $row['id'] . ")'>ØªØ­Ø¯ÙŠØ¯ ØªØ§Ø±ÙŠØ® Ø§Ù„Ø§Ù…ØªØ­Ø§Ù†</button>";
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

</body>
</html>
</body>
</html>

