<?php
session_start();
include 'db_connection.php';

// Ensure the user is logged in as a professor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'professor') {
    header('Location: login.php');
    exit;
}

$professor_id = $_SESSION['user_id'];

// Fetch all requests for this professor, except those already accepted
$sql_requests = "SELECT exu.id, subjects.name AS subject_name, exu.description, exu.file_path, exu.status, exu.created_at
                 FROM exu 
                 JOIN subjects ON exu.subject_id = subjects.id 
                 WHERE exu.professor_id = ? AND exu.status != 'مقبول'";
$stmt = $conn->prepare($sql_requests);
$stmt->bind_param("i", $professor_id);
$stmt->execute();
$result_requests = $stmt->get_result();

// Handle the form actions for accepting or rejecting requests
if (isset($_POST['action']) && isset($_POST['request_id'])) {
    $request_id = $_POST['request_id'];
    $action = $_POST['action'];
    
    // Set status based on the action
    $status = ($action == 'accept') ? 'مقبول' : 'مرفوض';

    // Update request status
    $sql_update = "UPDATE exu SET status = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("si", $status, $request_id);

    if ($stmt_update->execute()) {
        $response = ['success' => true, 'message' => $action == 'accept' ? 'تم قبول الطلب بنجاح' : 'تم رفض الطلب بنجاح'];
    } else {
        $response = ['success' => false, 'message' => 'حدث خطأ أثناء تحديث الطلب'];
    }

    echo json_encode($response);
    exit;
}

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>الطلبات المقدمة</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f9f9f9; padding: 20px; }
        h2 { text-align: center; color: #333; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: center; }
        th { background-color: #00796b; color: white; }
        .accept-btn, .reject-btn { padding: 5px 10px; }
        .status-rejected { color: red; }
    </style>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>
    <h2>الطلبات المقدمة</h2>
    <table id="requestsTable">
        <tr>
            <th>المادة</th>
            <th>الوصف</th>
            <th>الملف</th>
            <th>الحالة</th>
            <th>تاريخ التقديم</th>
            <th>قبول</th>
            <th>رفض</th>
        </tr>
        <?php
        if ($result_requests->num_rows > 0) {
            while ($row = $result_requests->fetch_assoc()) {
                echo "<tr id='row-{$row['id']}'>";
                echo "<td>" . htmlspecialchars($row['subject_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                echo "<td><a href='" . htmlspecialchars($row['file_path']) . "' target='_blank'>عرض الملف</a></td>";
                echo "<td class='status " . ($row['status'] == 'مرفوض' ? 'status-rejected' : '') . "'>" . htmlspecialchars($row['status']) . "</td>";
                echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
                echo "<td><button class='accept-btn' data-id='" . $row['id'] . "'>قبول</button></td>";
                echo "<td><button class='reject-btn' data-id='" . $row['id'] . "'>رفض</button></td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='7'>لا توجد طلبات بعد.</td></tr>";
        }
        ?>
    </table>

    <script>
    $(document).ready(function() {
        $('.accept-btn, .reject-btn').click(function() {
            var button = $(this);
            var requestId = button.data('id');
            var action = button.hasClass('accept-btn') ? 'accept' : 'reject';
            var row = button.closest('tr');
            
            // Disable buttons while processing
            button.prop('disabled', true);

            $.ajax({
                url: '<?php echo $_SERVER['PHP_SELF']; ?>',
                type: 'POST',
                data: {
                    action: action,
                    request_id: requestId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        if (action === 'accept') {
                            row.fadeOut(500, function() {
                                $(this).remove();
                                if ($('#requestsTable tr').length === 1) {
                                    $('#requestsTable').append('<tr><td colspan="7">لا توجد طلبات بعد.</td></tr>');
                                }
                            });
                        } else {
                            var statusCell = row.find('.status');
                            statusCell.text('مرفوض');
                            statusCell.addClass('status-rejected');
                            row.find('button').prop('disabled', true);
                        }
                    } else {
                        alert(response.message);
                        button.prop('disabled', false); // Re-enable button on error
                    }
                },
                error: function() {
                    alert('حدث خطأ أثناء الاتصال بالخادم');
                    button.prop('disabled', false); // Re-enable button on error
                }
            });
        });
    });
    </script>
</body>
</html>
