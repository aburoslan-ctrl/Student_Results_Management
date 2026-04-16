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

$stmt = $connect->prepare("SELECT id, subject_name FROM subjects ORDER BY id DESC");
$stmt->execute();
$result = $stmt->get_result();

$subjects = [];
while ($row = $result->fetch_assoc()) {
    $subjects[] = $row;
}

respondOK([
    "subjects" => $subjects,
    "total"    => count($subjects)
], "Subjects fetched successfully.");

$stmt->close();
?>
