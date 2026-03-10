<?php
$method = "POST";
$cache = "no-cache";
include "../../head.php"; // your common header/API functions

// Validate API token (only authorized users can add teachers)
$user = ValidateAPITokenSentIN();

// Required fields
$required_fields = ['username', 'email', 'password', 'fullname'];

foreach ($required_fields as $field) {
    if (!isset($_POST[$field])) {
        respondBadRequest("Field '$field' is required.");
    }
}

// Sanitize inputs
$username = trim(cleanme($_POST['username']));
$email    = trim(cleanme($_POST['email']));
$password = trim(cleanme($_POST['password']));
$fullname = trim(cleanme($_POST['fullname']));

// Validate fields are not empty
if (empty($username) || empty($email) || empty($password) || empty($fullname)) {
    respondBadRequest("Fields cannot be empty.");
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respondBadRequest("Invalid email format.");
}

// Optional: Validate password length (min 6 characters)
if (strlen($password) < 6) {
    respondBadRequest("Password must be at least 6 characters.");
}

// Check for duplicate username or email
$check = $connect->prepare("SELECT id FROM teachers WHERE username = ? OR email = ?");
$check->bind_param("ss", $username, $email);
$check->execute();
$result = $check->get_result();
if ($result->num_rows > 0) {
    respondBadRequest("Username or email already exists.");
}

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert teacher into database
$stmt = $connect->prepare("INSERT INTO teachers (username, email, password, fullname) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $username, $email, $hashed_password, $fullname);

if ($stmt->execute()) {
    respondOK([], "Teacher added successfully.");
} else {
    respondBadRequest("Failed to add teacher. Please try again.");
}
?>