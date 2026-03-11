<?php
$method = "POST";
$cache  = "no-cache";
include "../../head.php";

// Validate API token
$user = ValidateAPITokenSentIN();
$user_id = $user->usertoken;

// Check if user ID is valid
if (!isset($user_id) || input_is_invalid($user_id) || !is_numeric($user_id)) {
    respondUnauthorized();
}
$user_id = (int)$user_id;

// Check if result_id is provided
if (!isset($_POST['result_id'])) {
    respondBadRequest("Result ID is required.");
}

$result_id = cleanme($_POST['result_id']);

if (!is_numeric($result_id)) {
    respondBadRequest("Invalid result ID.");
}

$result_id = (int)$result_id;

// Optional: Check if result exists
$check = $connect->prepare("SELECT id FROM results WHERE id = ?");
$check->bind_param("i", $result_id);
$check->execute();
$check_result = $check->get_result();

if ($check_result->num_rows === 0) {
    respondBadRequest("Result not found.");
}

// Optional: Only admin or teacher can delete
// if (!in_array($user->role, ['admin', 'teacher'])) {
//     respondUnauthorized("You are not authorized to delete this result.");
// }

// Delete the result
$delete = $connect->prepare("DELETE FROM results WHERE id = ?");
$delete->bind_param("i", $result_id);

if ($delete->execute()) {
    respondOK([], "Result deleted successfully.");
} else {
    respondBadRequest("Failed to delete result. Please try again.");
}

// Close statements
$check->close();
$delete->close();
?>