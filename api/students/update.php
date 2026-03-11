<?php
$method = "POST";
$cache  = "no-cache";
include "../../head.php";

// Validate API token
$user = ValidateAPITokenSentIN();

// Check if student ID is provided
if (!isset($_POST['student_id']) || !is_numeric($_POST['student_id']) || (int)$_POST['student_id'] <= 0) {
    respondBadRequest("Valid student ID is required.");
}

$student_id = (int)cleanme($_POST['student_id']);

// Check if student exists
$check = $connect->prepare("SELECT * FROM students WHERE id = ?");
$check->bind_param("i", $student_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {
    respondBadRequest("Student not found.");
}

$current = $result->fetch_assoc();
$role = $user->role;

// Optional: Only admin or teacher can update (depending on your roles)
if ($role !== 'admin' && $role !== 'teacher') {
    respondUnauthorized("You are not authorized to update this student.");
}

// Fields that can be updated
$first_name = isset($_POST['first_name']) ? trim(cleanme($_POST['first_name'])) : $current['first_name'];
$last_name  = isset($_POST['last_name'])  ? trim(cleanme($_POST['last_name']))  : $current['last_name'];
$class_id   = isset($_POST['class_id'])   ? cleanme($_POST['class_id'])       : $current['class_id'];
$gender     = isset($_POST['gender'])     ? cleanme($_POST['gender'])         : $current['gender'];

// Validations
if (input_is_invalid($first_name) || input_is_invalid($last_name)) {
    respondBadRequest("First name and last name cannot be empty.");
}

if (!in_array($gender, ['Male', 'Female'])) {
    respondBadRequest("Invalid gender value.");
}

if (!is_numeric($class_id)) {
    respondBadRequest("Class ID must be numeric.");
}
elseif ($class_id <= 0) {
    respondBadRequest("Class ID must be a positive integer.");
}   

// Prepare update statement
$stmt = $connect->prepare("UPDATE students SET first_name = ?, last_name = ?, gender = ?, class_id = ?, updated_at = NOW() WHERE id = ?");
$stmt->bind_param("sssii", $first_name, $last_name, $gender, $class_id, $student_id);

// Execute update
if ($stmt->execute()) {
    respondOK([], "Student updated successfully.");
} else {
    respondBadRequest("Update failed. Please try again.");
}
?>