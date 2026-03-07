<?php
$method = "POST";
$cache  = "no-cache";
include "../../head.php";

$user = ValidateAPITokenSentIN();

if (!isset($_POST['subject_id'])) {
    respondBadRequest("Subject ID is required.");
}

$subject_id = cleanme($_POST['subject_id']);

if (!is_numeric($subject_id)) {
    respondBadRequest("Invalid subject ID.");
}

/* CHECK SUBJECT EXISTS */
$check = $connect->prepare("SELECT id FROM subjects WHERE id = ?");
$check->bind_param("i", $subject_id);
$check->execute();

if ($check->get_result()->num_rows == 0) {
    respondBadRequest("Subject not found.");
}

/* CHECK IF RESULTS EXIST */
$result_check = $connect->prepare("SELECT id FROM results WHERE subject_id = ?");
$result_check->bind_param("i", $subject_id);
$result_check->execute();

if ($result_check->get_result()->num_rows > 0) {
    respondBadRequest("Cannot delete subject. Results already exist for it.");
}

/* DELETE */
$stmt = $connect->prepare("DELETE FROM subjects WHERE id = ?");
$stmt->bind_param("i", $subject_id);

if ($stmt->execute()) {
    respondOK([], "Subject deleted successfully.");
} else {
    respondBadRequest("Delete failed.");
}
?>