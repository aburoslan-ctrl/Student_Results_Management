<?php
$method = "POST";
$cache = "no-cache";
include "../../head.php";


if (!isset($_POST['student_id'])) {
    respondBadRequest("Student ID required.");
}


$student_id = cleanme($_POST['student_id']);

    // $datasentin=ValidateAPITokenSentIN();
    // $user_id=$datasentin->usertoken;

if (!is_numeric($student_id)) {
    respondBadRequest("Invalid ID.");
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