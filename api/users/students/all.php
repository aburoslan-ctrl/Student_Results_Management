<?php
$method = "GET";
$cache  = "no-cache";
include "../../../head.php";

// Validate token
$datasentin = ValidateAPITokenSentIN();
$user_id = $datasentin->usertoken;

if (!isset($user_id) || input_is_invalid($user_id) || !is_numeric($user_id)) {
    respondUnauthorized();
}

$stmt = $connect->prepare("
    SELECT
        s.id,
        s.admission_no,
        s.first_name,
        s.last_name,
        s.gender,
        c.class_name
    FROM students s
    JOIN classes c ON s.class_id = c.id
    ORDER BY s.id DESC
");

$stmt->execute();
$result = $stmt->get_result();

$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

respondOK([
    "students" => $students,
    "total"    => count($students)
], "Students fetched successfully.");

$stmt->close();
?>
