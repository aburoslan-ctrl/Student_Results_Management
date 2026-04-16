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

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    respondBadRequest("Valid teacher ID is required.");
}

$teacher_id = (int)cleanme($_POST['id']);

// Get current teacher data
$check = $connect->prepare("SELECT * FROM teachers WHERE id = ?");
$check->bind_param("i", $teacher_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {
    respondBadRequest("Teacher not found.");
}

$current = $result->fetch_assoc();

$username = isset($_POST['username']) ? trim(cleanme($_POST['username'])) : $current['username'];
$email    = isset($_POST['email'])    ? trim(cleanme($_POST['email']))    : $current['email'];
$password = isset($_POST['password']) ? trim(cleanme($_POST['password'])) : null;
$fullname = isset($_POST['fullname']) ? trim(cleanme($_POST['fullname'])) : $current['fullname'];

if (empty($username) || empty($email) || empty($fullname)) {
    respondBadRequest("Username, email, and fullname cannot be empty.");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respondBadRequest("Invalid email format.");
}

if ($password !== null && strlen($password) < 6) {
    respondBadRequest("Password must be at least 6 characters.");
}

// Check duplicate username/email for other teachers
$check_dup = $connect->prepare("SELECT id FROM teachers WHERE (username = ? OR email = ?) AND id != ?");
$check_dup->bind_param("ssi", $username, $email, $teacher_id);
$check_dup->execute();

if ($check_dup->get_result()->num_rows > 0) {
    respondBadRequest("Username or email already exists.");
}

if ($password !== null) {
    $password = password_hash($password, PASSWORD_DEFAULT);
} else {
    $password = $current['password'];
}

$update = $connect->prepare("UPDATE teachers SET username = ?, email = ?, password = ?, fullname = ?, updated_at = NOW() WHERE id = ?");
$update->bind_param("ssssi", $username, $email, $password, $fullname, $teacher_id);

if ($update->execute()) {
    respondOK([], "Teacher updated successfully.");
} else {
    respondBadRequest("Failed to update teacher. Please try again.");
}
?>
