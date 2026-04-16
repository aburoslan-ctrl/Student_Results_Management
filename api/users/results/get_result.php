<?php
$method = "GET";
$cache  = "no-cache";
include "../../../head.php";

if (!isset($_GET['student_id'])) {
    respondBadRequest("Student ID required.");
}

$datasentin = ValidateAPITokenSentIN();
$user_id = $datasentin->usertoken;

if (!isset($user_id) || input_is_invalid($user_id) || !is_numeric($user_id)) {
    respondUnauthorized();
}

$id = cleanme($_GET['student_id']);

if (!is_numeric($id) || (int)$id <= 0) {
    respondBadRequest("Valid student ID is required.");
}

$id = (int)$id;

$sql = "SELECT subjects.subject_name, term, ca_score1, ca_score2, exam_score, total, grade
        FROM results
        JOIN subjects ON results.subject_id = subjects.id
        WHERE student_id = ?";

$stmt = $connect->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

respondOK([
    "results" => $data,
    "total"   => count($data)
], "Results fetched.");
?>
