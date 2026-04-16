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

if (!isset($_POST['id'])) {
    respondBadRequest("Teacher ID is required.");
}

$teacher_id = cleanme($_POST['id']);

if (!is_numeric($teacher_id) || (int)$teacher_id <= 0) {
    respondBadRequest("Valid teacher ID is required.");
}

$teacher_id = (int)$teacher_id;

// Check if teacher exists
$check = $connect->prepare("SELECT id FROM teachers WHERE id = ?");
$check->bind_param("i", $teacher_id);
$check->execute();

if ($check->get_result()->num_rows === 0) {
    respondBadRequest("Teacher not found.");
}

$stmt = $connect->prepare("DELETE FROM teachers WHERE id = ?");
$stmt->bind_param("i", $teacher_id);

if ($stmt->execute()) {
    respondOK([], "Teacher deleted successfully.");
} else {
    respondBadRequest("Failed to delete teacher.");
}
?>
