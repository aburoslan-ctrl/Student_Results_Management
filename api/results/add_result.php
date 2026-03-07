<?php
$method = "POST";
$cache = "no-cache";
include "../../head.php";

$user = ValidateAPITokenSentIN();

// Validate required POST fields
if (!isset($_POST['student_id'], $_POST['subject_id'], $_POST['term'], $_POST['ca_score1'], $_POST['ca_score2'], $_POST['exam_score'])) {
    respondBadRequest("All fields required.");
}

// Clean and assign variables
$student_id = cleanme($_POST['student_id']);
$subject_id = cleanme($_POST['subject_id']);
$term       = cleanme($_POST['term']);
$ca1        = cleanme($_POST['ca_score1']);
$ca2        = cleanme($_POST['ca_score2']);
$exam       = cleanme($_POST['exam_score']);

// Validate numeric fields
if (!is_numeric($student_id) || !is_numeric($subject_id)) respondBadRequest("Invalid ID.");
if (!is_numeric($ca1) || !is_numeric($ca2) || !is_numeric($exam)) respondBadRequest("Scores must be numeric.");

// Calculate total
$total = $ca1 + $ca2 + $exam;

// Simple grading
if ($total >= 70) $grade = "A";
elseif ($total >= 60) $grade = "B";
elseif ($total >= 50) $grade = "C";
elseif ($total >= 45) $grade = "D";
elseif ($total >= 40) $grade = "E";
else $grade = "F";

// Prepare and execute query
$stmt = $connect->prepare("
    INSERT INTO results 
        (student_id, subject_id, term, ca_score1, ca_score2, exam_score, total, grade) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param("iissiiis", $student_id, $subject_id, $term, $ca1, $ca2, $exam, $total, $grade);

if ($stmt->execute()) {
    respondOK([], "Result added successfully.");
} else {
    respondBadRequest("Failed to add result: " . $stmt->error);
}

$stmt->close();
?>