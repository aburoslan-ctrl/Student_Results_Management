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

if (!isset($_POST['student_id'])) {
    respondBadRequest("Student ID required.");
}

$student_id = cleanme($_POST['student_id']);

if (!is_numeric($student_id) || (int)$student_id <= 0) {
    respondBadRequest("Valid student ID is required.");
}

$student_id = (int)$student_id;

// Check if student exists
$check = $connect->prepare("SELECT id FROM students WHERE id = ?");
$check->bind_param("i", $student_id);
$check->execute();

if ($check->get_result()->num_rows == 0) {
    respondBadRequest("Student not found.");
}

// Check if student has results
$result_check = $connect->prepare("SELECT id FROM results WHERE student_id = ?");
$result_check->bind_param("i", $student_id);
$result_check->execute();

if ($result_check->get_result()->num_rows > 0) {
    respondBadRequest("Cannot delete student. Results are assigned to them.");
}

$stmt = $connect->prepare("DELETE FROM students WHERE id = ?");
$stmt->bind_param("i", $student_id);

if ($stmt->execute()) {
    respondOK([], "Student deleted successfully.");
} else {
    respondBadRequest("Delete failed.");
}
?>
