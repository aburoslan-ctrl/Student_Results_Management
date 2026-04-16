<?php

$method = "GET";
$cache  = "no-cache";
include "../../../head.php";

// Validate token
$datasentin = ValidateAPITokenSentIN();
$user_id = $datasentin->usertoken;

if (!isset($user_id) || input_is_invalid($user_id) || !is_numeric($user_id)) {
    respondUnauthorized();
    exit;
}

// Fetch admin profile
$stmt = $connect->prepare("
    SELECT id, username, email, role, created_at
    FROM users
    WHERE id = ? AND role = 'admin'
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $profile = $result->fetch_assoc();
    respondOK(["user" => $profile], "Profile fetched successfully.");
} else {
    respondBadRequest("Admin not found.");
    exit;
}

?>
