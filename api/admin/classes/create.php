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

if (!isset($_POST['class_name'])) {
    respondBadRequest("Class name is required.");
}

$class_name = cleanme($_POST['class_name']);

if (input_is_invalid($class_name)) {
    respondBadRequest("Class name cannot be empty.");
}

if (strlen($class_name) < 2 || strlen($class_name) > 50) {
    respondBadRequest("Class name must be between 2 and 50 characters.");
}

if (!preg_match("/^[a-zA-Z0-9 ]+$/", $class_name)) {
    respondBadRequest("Class name contains invalid characters.");
}

/* CHECK DUPLICATE */
$check = $connect->prepare("SELECT id FROM classes WHERE class_name = ?");
$check->bind_param("s", $class_name);
$check->execute();

if ($check->get_result()->num_rows > 0) {
    respondBadRequest("Class already exists.");
}

/* INSERT */
$stmt = $connect->prepare("INSERT INTO classes (class_name) VALUES (?)");
$stmt->bind_param("s", $class_name);

if ($stmt->execute()) {
    respondOK([], "Class added successfully.");
} else {
    respondBadRequest("Failed to add class.");
}
?>
