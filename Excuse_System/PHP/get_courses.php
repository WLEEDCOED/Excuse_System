<?php
include 'includes/db_connection.php';

$semester_id = $_POST['semester_id'];
$student_id = $_POST['student_id'];

$sql_courses = "SELECT c.id, c.name
                FROM student_courses sc
                JOIN course c ON sc.course_id = c.id
                WHERE sc.student_id = ? AND c.semester_id = ?";
$stmt = $conn->prepare($sql_courses);
$stmt->bind_param("ii", $student_id, $semester_id);
$stmt->execute();
$result = $stmt->get_result();

$courses = [];
while ($row = $result->fetch_assoc()) {
    $courses[] = $row;
}

echo json_encode($courses);
?>
