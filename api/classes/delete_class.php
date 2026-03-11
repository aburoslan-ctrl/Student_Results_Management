<?php
$method = "POST";
$cache  = "no-cache";
include "../../head.php";

$user = ValidateAPITokenSentIN();
$user_id = $user->usertoken;

if (!isset($_POST['class_id'])) {
    respondBadRequest("Class ID required.");
}

$class_id = cleanme($_POST['class_id']);

if (!is_numeric($class_id)) {
    respondBadRequest("Invalid class ID.");
}

/* CHECK CLASS EXISTS */
$check = $connect->prepare("SELECT id FROM classes WHERE id = ?");
$check->bind_param("i", $class_id);
$check->execute();

if ($check->get_result()->num_rows == 0) {
    respondBadRequest("Class not found.");
}
//check if user is admin 
if ($user_id !== 'admin') {
    respondUnauthorized("You are not authorized to delete this class.");
}

/* CHECK IF STUDENTS BELONG TO THIS CLASS */
$student_check = $connect->prepare("SELECT id FROM students WHERE class_id = ?");
$student_check->bind_param("i", $class_id);
$student_check->execute();

if ($student_check->get_result()->num_rows > 0) {
    respondBadRequest("Cannot delete class. Students are assigned to it.");
}

/* DELETE */
$stmt = $connect->prepare("DELETE FROM classes WHERE id = ?");
$stmt->bind_param("i", $class_id);

if ($stmt->execute()) {
    respondOK([], "Class deleted successfully.");
} else {
    respondBadRequest("Delete failed.");
}
?>