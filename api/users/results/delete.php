<?php
$method = "POST";
$cache  = "no-cache";
include "../../../head.php";

// Validate token
$user = ValidateAPITokenSentIN();
$user_id = $user->usertoken;

if (!isset($user_id) || input_is_invalid($user_id) || !is_numeric($user_id)) {
    respondUnauthorized();
}

if (!isset($_POST['result_id'])) {
    respondBadRequest("Result ID is required.");
}

$result_id = cleanme($_POST['result_id']);

if (!is_numeric($result_id) || (int)$result_id <= 0) {
    respondBadRequest("Valid result ID is required.");
}

$result_id = (int)$result_id;

// Check if result exists
$check = $connect->prepare("SELECT id FROM results WHERE id = ?");
$check->bind_param("i", $result_id);
$check->execute();

if ($check->get_result()->num_rows === 0) {
    respondBadRequest("Result not found.");
}

$delete = $connect->prepare("DELETE FROM results WHERE id = ?");
$delete->bind_param("i", $result_id);

if ($delete->execute()) {
    respondOK([], "Result deleted successfully.");
} else {
    respondBadRequest("Failed to delete result.");
}

$check->close();
$delete->close();
?>
