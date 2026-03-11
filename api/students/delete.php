<?php
$method = "POST";
$cache = "no-cache";
include "../../head.php";


if (!isset($_POST['student_id'])) {
    respondBadRequest("Student ID required.");
}


$student_id = cleanme($_POST['student_id']);

    $datasentin=ValidateAPITokenSentIN();
    $user_id=$datasentin->usertoken;

if (!is_numeric($student_id)) {
    respondBadRequest("Invalid ID.");
}
// check if student exists
$check = $connect->prepare("SELECT id FROM students WHERE id = ?");
$check->bind_param("i", $student_id);
$check->execute();
$result = $check->get_result();
if ($result->num_rows == 0) {
    respondBadRequest("Student not found.");
}
// check if user is admin

if ($user_id !== 'admin') {
    respondUnauthorized("You are not authorized to delete this student.");
}
//. check if student has results
$result_check = $connect->prepare("SELECT id FROM results WHERE student_id = ?");
$result_check->bind_param("i", $student_id);
$result_check->execute();
if ($result_check->get_result()->num_rows > 0) {
    respondBadRequest("Cannot delete student. Results are assigned to them.");
}
$stmt = $connect->prepare("DELETE FROM students WHERE id = ?");
$stmt->bind_param("i", $student_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows == 0) {
        respondBadRequest("Student not found.");
    }
    respondOK([], "Student deleted successfully.");
} else {
    respondBadRequest("Delete failed.");
}
?>