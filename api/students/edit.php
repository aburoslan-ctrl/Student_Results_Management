<?php
$method = "POST";
$cache = "no-cache";
include "../../head.php";

$user = ValidateAPITokenSentIN();

if (!isset($_POST['student_id'])) {
    respondBadRequest("Student ID is required.");
}

$student_id = cleanme($_POST['student_id']);

if (!is_numeric($student_id)) {
    respondBadRequest("Invalid student ID.");
}

/* CHECK IF STUDENT EXISTS */
$check = $connect->prepare("SELECT id FROM students WHERE id = ?");
$check->bind_param("i", $student_id);
$check->execute();
if ($check->get_result()->num_rows == 0) {
    respondBadRequest("Student not found.");
}

/* BUILD DYNAMIC UPDATE */
$fields = [];
$params = [];
$types  = "";

if (isset($_POST['first_name'])) {
    $fields[] = "first_name = ?";
    $params[] = cleanme($_POST['first_name']);
    $types .= "s";
}

if (isset($_POST['last_name'])) {
    $fields[] = "last_name = ?";
    $params[] = cleanme($_POST['last_name']);
    $types .= "s";
}

if (isset($_POST['class_id'])) {
    if (!is_numeric($_POST['class_id'])) {
        respondBadRequest("Invalid class ID.");
    }
    $fields[] = "class_id = ?";
    $params[] = $_POST['class_id'];
    $types .= "i";
}

if (empty($fields)) {
    respondBadRequest("No data to update.");
}

$sql = "UPDATE students SET " . implode(", ", $fields) . " WHERE id = ?";
$stmt = $connect->prepare($sql);

$types .= "i";
$params[] = $student_id;

$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    respondOK([], "Student updated successfully.");
} else {
    respondBadRequest("Update failed.");
}
?>