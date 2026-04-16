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

if (!isset($_POST['admission_no'], $_POST['first_name'], $_POST['last_name'], $_POST['gender'], $_POST['class_id'])) {
    respondBadRequest("All fields are required.");
}

$admission_no = cleanme($_POST['admission_no']);
$first_name   = cleanme($_POST['first_name']);
$last_name    = cleanme($_POST['last_name']);
$gender       = cleanme($_POST['gender']);
$class_id     = cleanme($_POST['class_id']);

if (input_is_invalid($admission_no) || input_is_invalid($first_name) || input_is_invalid($last_name)) {
    respondBadRequest("Fields cannot be empty.");
}

if (!in_array($gender, ['Male', 'Female'])) {
    respondBadRequest("Invalid gender value.");
}

if (!is_numeric($class_id) || (int)$class_id <= 0) {
    respondBadRequest("Class ID must be a positive integer.");
}

// Check class exists
$check_class = $connect->prepare("SELECT id FROM classes WHERE id = ?");
$check_class->bind_param("i", $class_id);
$check_class->execute();
if ($check_class->get_result()->num_rows == 0) {
    respondBadRequest("Class not found.");
}

if (strlen($first_name) < 2 || strlen($first_name) > 50 || strlen($last_name) < 2 || strlen($last_name) > 50) {
    respondBadRequest("First and last name must be between 2 and 50 characters.");
} elseif (!preg_match("/^[a-zA-Z]+$/", $first_name) || !preg_match("/^[a-zA-Z]+$/", $last_name)) {
    respondBadRequest("First and last name can only contain letters.");
} elseif (strlen($admission_no) < 2 || strlen($admission_no) > 20) {
    respondBadRequest("Admission number must be between 2 and 20 characters.");
} elseif (!preg_match("/^[a-zA-Z0-9]+$/", $admission_no)) {
    respondBadRequest("Admission number can only contain letters and numbers.");
}

/* CHECK DUPLICATE ADMISSION */
$check = $connect->prepare("SELECT id FROM students WHERE admission_no = ?");
$check->bind_param("s", $admission_no);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    respondBadRequest("Admission number already exists.");
}

/* INSERT */
$stmt = $connect->prepare("INSERT INTO students (admission_no, first_name, last_name, gender, class_id) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("ssssi", $admission_no, $first_name, $last_name, $gender, $class_id);

if ($stmt->execute()) {
    respondOK([], "Student added successfully.");
} else {
    respondBadRequest("Failed to add student.");
}
?>
