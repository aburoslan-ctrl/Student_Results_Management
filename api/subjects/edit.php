<?php
$method = "POST";
$cache  = "no-cache";
include "../../head.php";

$user = ValidateAPITokenSentIN();

if (!isset($_POST['subject_id'], $_POST['subject_name'])) {
    respondBadRequest("Subject ID and subject name are required.");
}

$subject_id   = cleanme($_POST['subject_id']);
$subject_name = cleanme($_POST['subject_name']);

if (!is_numeric($subject_id)) {
    respondBadRequest("Invalid subject ID.");
}

if (input_is_invalid($subject_name)) {
    respondBadRequest("Subject name cannot be empty.");
}

/* CHECK SUBJECT EXISTS */
$check = $connect->prepare("SELECT id FROM subjects WHERE id = ?");
$check->bind_param("i", $subject_id);
$check->execute();

if ($check->get_result()->num_rows == 0) {
    respondBadRequest("Subject not found.");
}

/* CHECK DUPLICATE NAME (EXCEPT CURRENT ID) */
$dup = $connect->prepare("SELECT id FROM subjects WHERE subject_name = ? AND id != ?");
$dup->bind_param("si", $subject_name, $subject_id);
$dup->execute();

if ($dup->get_result()->num_rows > 0) {
    respondBadRequest("Another subject with this name already exists.");
}

/* UPDATE */
$stmt = $connect->prepare("UPDATE subjects SET subject_name = ? WHERE id = ?");
$stmt->bind_param("si", $subject_name, $subject_id);

if ($stmt->execute()) {
    respondOK([], "Subject updated successfully.");
} else {
    respondBadRequest("Update failed.");
}
?>