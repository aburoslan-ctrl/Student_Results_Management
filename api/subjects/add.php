<?php
$method = "POST";
$cache  = "no-cache";
include "../../head.php";

$user = ValidateAPITokenSentIN();
$user_id = $user->usertoken;

if (!isset($_POST['subject_name'])) {
    respondBadRequest("Subject name is required.");
}

$subject_name = cleanme($_POST['subject_name']);

/* VALIDATION */
if (input_is_invalid($subject_name)) {
    respondBadRequest("Subject name cannot be empty.");
}

if (strlen($subject_name) < 2 || strlen($subject_name) > 100) {
    respondBadRequest("Subject name must be between 2 and 100 characters.");
}

/* Allow letters, numbers and spaces only */
if (!preg_match("/^[a-zA-Z0-9 ]+$/", $subject_name)) {
    respondBadRequest("Subject name contains invalid characters.");
}

/* CHECK DUPLICATE */
$check = $connect->prepare("SELECT id FROM subjects WHERE subject_name = ?");
$check->bind_param("s", $subject_name);
$check->execute();

if ($check->get_result()->num_rows > 0) {
    respondBadRequest("Subject already exists.");
}

/* INSERT */
$stmt = $connect->prepare("INSERT INTO subjects (subject_name) VALUES (?)");
$stmt->bind_param("s", $subject_name);

if ($stmt->execute()) {
    respondOK([], "Subject added successfully.");
} else {
    respondBadRequest("Failed to add subject.");
}
?>