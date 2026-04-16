<?php

$method = "POST";
$cache  = "no-cache";
include "../../../head.php";

// Validate token
$user = ValidateAPITokenSentIN();
$admin_id = $user->usertoken;

if (!isset($admin_id) || input_is_invalid($admin_id) || !is_numeric($admin_id)) {
    respondUnauthorized();
    exit;
}

// Admin only
$roleCheck = $connect->prepare("SELECT role FROM users WHERE id = ?");
$roleCheck->bind_param("i", $admin_id);
$roleCheck->execute();
$roleResult = $roleCheck->get_result()->fetch_assoc();

if (!$roleResult || $roleResult['role'] !== 'admin') {
    respondForbiddenAuthorized("Admin access required.");
    exit;
}

if (isset($_POST['id'])) {

    $target_id = cleanme($_POST['id']);

    if (input_is_invalid($target_id) || !is_numeric($target_id)) {
        respondBadRequest("A valid user ID is required.");
        exit;
    }

    $target_id = (int)$target_id;

    // Check if user exists
    $checkUser = $connect->prepare("SELECT id FROM users WHERE id = ?");
    $checkUser->bind_param("i", $target_id);
    $checkUser->execute();

    if ($checkUser->get_result()->num_rows === 0) {
        respondBadRequest("User not found.");
        exit;
    }

    $deleteUser = $connect->prepare("DELETE FROM users WHERE id = ?");
    $deleteUser->bind_param("i", $target_id);
    $deleteUser->execute();

    if ($deleteUser->affected_rows > 0) {
        respondOK([], "User deleted successfully.");
    } else {
        respondBadRequest("No changes made or delete failed.");
    }

} else {
    respondBadRequest("Invalid request. User ID is required.");
}

?>
