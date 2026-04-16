<?php
$method = "POST";
$cache  = "no-cache";
include "../../../head.php";

// Validate token
$user = ValidateAPITokenSentIN();
$user_id = $user->usertoken;

if (!isset($user_id) || input_is_invalid($user_id) || !is_numeric($user_id)) {
    respondUnauthorized();
    exit;
}

// Admin only
$roleCheck = $connect->prepare("SELECT role FROM users WHERE id = ?");
$roleCheck->bind_param("i", $user_id);
$roleCheck->execute();
$roleResult = $roleCheck->get_result()->fetch_assoc();

if (!$roleResult || $roleResult['role'] !== 'admin') {
    respondForbiddenAuthorized("Admin access required.");
    exit;
}

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

$first_name = isset($_POST['first_name']) ? trim(cleanme($_POST['first_name'])) : $current['first_name'];
$last_name  = isset($_POST['last_name'])  ? trim(cleanme($_POST['last_name']))  : $current['last_name'];
$class_id   = isset($_POST['class_id'])   ? cleanme($_POST['class_id'])         : $current['class_id'];
$gender     = isset($_POST['gender'])     ? cleanme($_POST['gender'])           : $current['gender'];

if (input_is_invalid($first_name) || input_is_invalid($last_name)) {
    respondBadRequest("First name and last name cannot be empty.");
}

if (!in_array($gender, ['Male', 'Female'])) {
    respondBadRequest("Invalid gender value.");
}

if (!is_numeric($class_id) || (int)$class_id <= 0) {
    respondBadRequest("Class ID must be a positive integer.");
}

$stmt = $connect->prepare("UPDATE students SET first_name = ?, last_name = ?, gender = ?, class_id = ?, updated_at = NOW() WHERE id = ?");
$stmt->bind_param("sssii", $first_name, $last_name, $gender, $class_id, $student_id);

if ($stmt->execute()) {
    respondOK([], "Student updated successfully.");
} else {
    respondBadRequest("Update failed. Please try again.");
}
?>
