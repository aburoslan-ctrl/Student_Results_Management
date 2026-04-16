<?php
$method = "POST";
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

if (!isset($_POST['result_id'])) {
    respondBadRequest("Result ID is required.");
}

$result_id = cleanme($_POST['result_id']);

if (!is_numeric($result_id) || (int)$result_id <= 0) {
    respondBadRequest("Valid result ID is required.");
}

$result_id = (int)$result_id;

// Fetch current result
$check = $connect->prepare("SELECT * FROM results WHERE id = ?");
$check->bind_param("i", $result_id);
$check->execute();
$checkResult = $check->get_result();

if ($checkResult->num_rows === 0) {
    respondBadRequest("Result not found.");
}

$current = $checkResult->fetch_assoc();

// Get updated values or keep current
$ca1  = isset($_POST['ca_score1'])  ? cleanme($_POST['ca_score1'])  : $current['ca_score1'];
$ca2  = isset($_POST['ca_score2'])  ? cleanme($_POST['ca_score2'])  : $current['ca_score2'];
$exam = isset($_POST['exam_score']) ? cleanme($_POST['exam_score']) : $current['exam_score'];
$term = isset($_POST['term'])       ? cleanme($_POST['term'])       : $current['term'];

// Validate scores
if (!is_numeric($ca1) || !is_numeric($ca2) || !is_numeric($exam)) {
    respondBadRequest("Scores must be numeric.");
} elseif ($ca1 < 0 || $ca1 > 20 || $ca2 < 0 || $ca2 > 20 || $exam < 0 || $exam > 60) {
    respondBadRequest("Scores out of valid range.");
} elseif (!in_array($term, ['First', 'Second', 'Third'])) {
    respondBadRequest("Invalid term value.");
}

// Recalculate total and grade
$total = $ca1 + $ca2 + $exam;

if ($total >= 70) $grade = "A";
elseif ($total >= 60) $grade = "B";
elseif ($total >= 50) $grade = "C";
elseif ($total >= 45) $grade = "D";
elseif ($total >= 40) $grade = "E";
else $grade = "F";

// Check for duplicate if term changed
if ($term !== $current['term']) {
    $dup = $connect->prepare("SELECT id FROM results WHERE student_id = ? AND subject_id = ? AND term = ? AND id != ?");
    $dup->bind_param("iisi", $current['student_id'], $current['subject_id'], $term, $result_id);
    $dup->execute();

    if ($dup->get_result()->num_rows > 0) {
        respondBadRequest("Result for this student, subject, and term already exists.");
    }
}

$stmt = $connect->prepare("UPDATE results SET term = ?, ca_score1 = ?, ca_score2 = ?, exam_score = ?, total = ?, grade = ? WHERE id = ?");
$stmt->bind_param("siiiisi", $term, $ca1, $ca2, $exam, $total, $grade, $result_id);

if ($stmt->execute()) {
    respondOK([], "Result updated successfully.");
} else {
    respondBadRequest("Failed to update result.");
}

$stmt->close();
?>
