<?php
$method = "POST";
$cache  = "no-cache";
include "../../head.php";

// Validate API token
$user = ValidateAPITokenSentIN();

// Check teacher ID
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

// Optional: Only admin can update
// if ($user->role !== 'admin') {
//     respondUnauthorized("You are not authorized to update this teacher.");
// }

// Updateable fields
$username = isset($_POST['username']) ? trim(cleanme($_POST['username'])) : $current['username'];
$email    = isset($_POST['email'])    ? trim(cleanme($_POST['email']))    : $current['email'];
$password = isset($_POST['password']) ? trim(cleanme($_POST['password'])) : null;
$fullname = isset($_POST['fullname']) ? trim(cleanme($_POST['fullname'])) : $current['fullname'];

// Validate required fields
if (empty($username) || empty($email) || empty($fullname)) {
    respondBadRequest("Username, email, and fullname cannot be empty.");
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respondBadRequest("Invalid email format.");
}

// Optional: Validate password length if provided
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

// Hash password if updated
if ($password !== null) {
    $password = password_hash($password, PASSWORD_DEFAULT);
} else {
    $password = $current['password']; // keep existing password hash
}

// Update teacher
$update = $connect->prepare("UPDATE teachers SET username = ?, email = ?, password = ?, fullname = ?, updated_at = NOW() WHERE id = ?");
$update->bind_param("ssssi", $username, $email, $password, $fullname, $teacher_id);

if ($update->execute()) {
    respondOK([], "Teacher updated successfully.");
} else {
    respondBadRequest("Failed to update teacher. Please try again.");
}
?>