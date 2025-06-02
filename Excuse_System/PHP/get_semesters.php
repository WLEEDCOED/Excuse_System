<?php
include 'includes/db_connection.php';

$academic_year_id = $_POST['academic_year_id'];
$sql_semesters = "SELECT id, name FROM semesters WHERE academic_year_id = ?";
$stmt = $conn->prepare($sql_semesters);
$stmt->bind_param("i", $academic_year_id);
$stmt->execute();
$result = $stmt->get_result();

$semesters = [];
while ($row = $result->fetch_assoc()) {
    $semesters[] = $row;
}

echo json_encode($semesters);
?>
