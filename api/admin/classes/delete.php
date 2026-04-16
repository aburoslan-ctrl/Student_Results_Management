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

if (!isset($_POST['class_id'])) {
    respondBadRequest("Class ID is required.");
}

$class_id = cleanme($_POST['class_id']);

if (!is_numeric($class_id) || (int)$class_id <= 0) {
    respondBadRequest("Valid class ID is required.");
}

$class_id = (int)$class_id;

// Check class exists
$check = $connect->prepare("SELECT id FROM classes WHERE id = ?");
$check->bind_param("i", $class_id);
$check->execute();

if ($check->get_result()->num_rows === 0) {
    respondBadRequest("Class not found.");
}

// Check if students are assigned
$studentCheck = $connect->prepare("SELECT id FROM students WHERE class_id = ?");
$studentCheck->bind_param("i", $class_id);
$studentCheck->execute();

if ($studentCheck->get_result()->num_rows > 0) {
    respondBadRequest("Cannot delete class. Students are assigned to it.");
}

$stmt = $connect->prepare("DELETE FROM classes WHERE id = ?");
$stmt->bind_param("i", $class_id);

if ($stmt->execute()) {
    respondOK([], "Class deleted successfully.");
} else {
    respondBadRequest("Failed to delete class.");
}
?>
