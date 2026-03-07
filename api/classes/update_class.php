<?php
$method = "POST";
$cache  = "no-cache";
include "../../head.php";

$user = ValidateAPITokenSentIN();

if (!isset($_POST['class_id'], $_POST['class_name'])) {
    respondBadRequest("Class ID and class name required.");
}

$class_id   = cleanme($_POST['class_id']);
$class_name = cleanme($_POST['class_name']);

if (!is_numeric($class_id)) {
    respondBadRequest("Invalid class ID.");
}

if (input_is_invalid($class_name)) {
    respondBadRequest("Class name cannot be empty.");
}

/* CHECK CLASS EXISTS */
$check = $connect->prepare("SELECT id FROM classes WHERE id = ?");
$check->bind_param("i", $class_id);
$check->execute();

if ($check->get_result()->num_rows == 0) {
    respondBadRequest("Class not found.");
}

/* CHECK DUPLICATE NAME (EXCEPT CURRENT ID) */
$dup = $connect->prepare("SELECT id FROM classes WHERE class_name = ? AND id != ?");
$dup->bind_param("si", $class_name, $class_id);
$dup->execute();

if ($dup->get_result()->num_rows > 0) {
    respondBadRequest("Another class with this name already exists.");
}

/* UPDATE */
$stmt = $connect->prepare("UPDATE classes SET class_name = ? WHERE id = ?");
$stmt->bind_param("si", $class_name, $class_id);

if ($stmt->execute()) {
    respondOK([], "Class updated successfully.");
} else {
    respondBadRequest("Update failed.");
}
?>