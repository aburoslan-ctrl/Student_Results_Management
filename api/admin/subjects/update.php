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

if (!isset($_POST['subject_id']) || !isset($_POST['subject_name'])) {
    respondBadRequest("Subject ID and subject name are required.");
}

$subject_id   = cleanme($_POST['subject_id']);
$subject_name = cleanme($_POST['subject_name']);

if (!is_numeric($subject_id) || (int)$subject_id <= 0) {
    respondBadRequest("Valid subject ID is required.");
}

$subject_id = (int)$subject_id;

if (input_is_invalid($subject_name)) {
    respondBadRequest("Subject name cannot be empty.");
}

if (strlen($subject_name) < 2 || strlen($subject_name) > 100) {
    respondBadRequest("Subject name must be between 2 and 100 characters.");
}

if (!preg_match("/^[a-zA-Z0-9 ]+$/", $subject_name)) {
    respondBadRequest("Subject name contains invalid characters.");
}

// Check subject exists
$check = $connect->prepare("SELECT id FROM subjects WHERE id = ?");
$check->bind_param("i", $subject_id);
$check->execute();

if ($check->get_result()->num_rows === 0) {
    respondBadRequest("Subject not found.");
}

// Check duplicate name
$dup = $connect->prepare("SELECT id FROM subjects WHERE subject_name = ? AND id != ?");
$dup->bind_param("si", $subject_name, $subject_id);
$dup->execute();

if ($dup->get_result()->num_rows > 0) {
    respondBadRequest("Subject name already exists.");
}

$stmt = $connect->prepare("UPDATE subjects SET subject_name = ? WHERE id = ?");
$stmt->bind_param("si", $subject_name, $subject_id);

if ($stmt->execute()) {
    respondOK([], "Subject updated successfully.");
} else {
    respondBadRequest("Failed to update subject.");
}
?>
