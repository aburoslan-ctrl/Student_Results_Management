<?php
$method = "POST";
$cache = "no-cache";
include "../../head.php";

$user = ValidateAPITokenSentIN();

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

if (!in_array($gender, ['Male','Female'])) {
    respondBadRequest("Invalid gender value.");
}

if (!is_numeric($class_id)) {
    respondBadRequest("Class ID must be numeric.");
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