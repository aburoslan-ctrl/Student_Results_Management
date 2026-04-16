<?php
$method = "GET";
$cache  = "no-cache";
include "../../../head.php";

// Validate token
$user = ValidateAPITokenSentIN();
$user_id = $user->usertoken;

if (!isset($user_id) || input_is_invalid($user_id) || !is_numeric($user_id)) {
    respondUnauthorized();
    exit;
}

// Admin only
$roleCheck = $connect->prepare("SELECT role FROM users WHERE id = ?");
$roleCheck->bind_param("i", $user_id);
$roleCheck->execute();
$roleResult = $roleCheck->get_result()->fetch_assoc();

if (!$roleResult || $roleResult['role'] !== 'admin') {
    respondForbiddenAuthorized("Admin access required.");
    exit;
}

$stmt = $connect->prepare("
    SELECT
        r.id,
        s.admission_no,
        CONCAT(s.first_name, ' ', s.last_name) AS student_name,
        sub.subject_name,
        r.term,
        r.ca_score1,
        r.ca_score2,
        r.exam_score,
        r.total,
        r.grade
    FROM results r
    JOIN students s ON r.student_id = s.id
    JOIN subjects sub ON r.subject_id = sub.id
    ORDER BY r.id DESC
");

$stmt->execute();
$result = $stmt->get_result();

$results = [];
while ($row = $result->fetch_assoc()) {
    $results[] = $row;
}

respondOK([
    "results" => $results,
    "total"   => count($results)
], "Results fetched successfully.");

$stmt->close();
?>
