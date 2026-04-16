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

if (!isset($_POST['subject_id'])) {
    respondBadRequest("Subject ID is required.");
}

$subject_id = cleanme($_POST['subject_id']);

if (!is_numeric($subject_id) || (int)$subject_id <= 0) {
    respondBadRequest("Valid subject ID is required.");
}

$subject_id = (int)$subject_id;

// Check subject exists
$check = $connect->prepare("SELECT id FROM subjects WHERE id = ?");
$check->bind_param("i", $subject_id);
$check->execute();

if ($check->get_result()->num_rows === 0) {
    respondBadRequest("Subject not found.");
}

// Check if results are assigned
$resultCheck = $connect->prepare("SELECT id FROM results WHERE subject_id = ?");
$resultCheck->bind_param("i", $subject_id);
$resultCheck->execute();

if ($resultCheck->get_result()->num_rows > 0) {
    respondBadRequest("Cannot delete subject. Results are assigned to it.");
}

$stmt = $connect->prepare("DELETE FROM subjects WHERE id = ?");
$stmt->bind_param("i", $subject_id);

if ($stmt->execute()) {
    respondOK([], "Subject deleted successfully.");
} else {
    respondBadRequest("Failed to delete subject.");
}
?>
