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

$required_fields = ['username', 'email', 'password', 'fullname'];

foreach ($required_fields as $field) {
    if (!isset($_POST[$field])) {
        respondBadRequest("Field '$field' is required.");
    }
}

$username = trim(cleanme($_POST['username']));
$email    = trim(cleanme($_POST['email']));
$password = trim(cleanme($_POST['password']));
$fullname = trim(cleanme($_POST['fullname']));

if (empty($username) || empty($email) || empty($password) || empty($fullname)) {
    respondBadRequest("Fields cannot be empty.");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respondBadRequest("Invalid email format.");
}

if (strlen($password) < 6) {
    respondBadRequest("Password must be at least 6 characters.");
}

// Check for duplicate username or email
$check = $connect->prepare("SELECT id FROM teachers WHERE username = ? OR email = ?");
$check->bind_param("ss", $username, $email);
$check->execute();

if ($check->get_result()->num_rows > 0) {
    respondBadRequest("Username or email already exists.");
}

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$stmt = $connect->prepare("INSERT INTO teachers (username, email, password, fullname) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $username, $email, $hashed_password, $fullname);

if ($stmt->execute()) {
    respondOK([], "Teacher added successfully.");
} else {
    respondBadRequest("Failed to add teacher. Please try again.");
}
?>
