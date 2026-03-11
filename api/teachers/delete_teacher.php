<?php
$method = "POST";
$cache  = "no-cache";
include "../../head.php";

// Validate API token
$user = ValidateAPITokenSentIN();
$user_id = $user->usertoken;
// Check if teacher ID is provided
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    respondBadRequest("Valid teacher ID is required.");
}

$teacher_id = (int)cleanme($_POST['id']);

// Check if teacher exists
$check = $connect->prepare("SELECT id FROM teachers WHERE id = ?");
$check->bind_param("i", $teacher_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {
    respondBadRequest("Teacher not found.");
}

// Optional: Only admin can delete (if you have roles)
if ($user_id !== 'admin') {
    respondUnauthorized("You are not authorized to delete this teacher.");
}


// Delete teacher
$delete = $connect->prepare("DELETE FROM teachers WHERE id = ?");
$delete->bind_param("i", $teacher_id);

if ($delete->execute()) {
    respondOK([], "Teacher deleted successfully.");
} else {
    respondBadRequest("Failed to delete teacher. Please try again.");
}
?>