<?php
$method = "GET";
$cache  = "no-cache";
include "../../../head.php";

// Validate token
$datasentin = ValidateAPITokenSentIN();
$user_id = $datasentin->usertoken;

if (!isset($user_id) || input_is_invalid($user_id) || !is_numeric($user_id)) {
    respondUnauthorized();
}

$stmt = $connect->prepare("SELECT id, class_name FROM classes ORDER BY id DESC");
$stmt->execute();
$result = $stmt->get_result();

$classes = [];
while ($row = $result->fetch_assoc()) {
    $classes[] = $row;
}

respondOK([
    "classes" => $classes,
    "total"   => count($classes)
], "Classes fetched successfully.");

$stmt->close();
?>
