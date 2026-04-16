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

if (!isset($_POST['class_id']) || !isset($_POST['class_name'])) {
    respondBadRequest("Class ID and class name are required.");
}

$class_id   = cleanme($_POST['class_id']);
$class_name = cleanme($_POST['class_name']);

if (!is_numeric($class_id) || (int)$class_id <= 0) {
    respondBadRequest("Valid class ID is required.");
}

$class_id = (int)$class_id;

if (input_is_invalid($class_name)) {
    respondBadRequest("Class name cannot be empty.");
}

if (strlen($class_name) < 2 || strlen($class_name) > 50) {
    respondBadRequest("Class name must be between 2 and 50 characters.");
}

if (!preg_match("/^[a-zA-Z0-9 ]+$/", $class_name)) {
    respondBadRequest("Class name contains invalid characters.");
}

// Check class exists
$check = $connect->prepare("SELECT id FROM classes WHERE id = ?");
$check->bind_param("i", $class_id);
$check->execute();

if ($check->get_result()->num_rows === 0) {
    respondBadRequest("Class not found.");
}

// Check duplicate name
$dup = $connect->prepare("SELECT id FROM classes WHERE class_name = ? AND id != ?");
$dup->bind_param("si", $class_name, $class_id);
$dup->execute();

if ($dup->get_result()->num_rows > 0) {
    respondBadRequest("Class name already exists.");
}

$stmt = $connect->prepare("UPDATE classes SET class_name = ? WHERE id = ?");
$stmt->bind_param("si", $class_name, $class_id);

if ($stmt->execute()) {
    respondOK([], "Class updated successfully.");
} else {
    respondBadRequest("Failed to update class.");
}
?>
