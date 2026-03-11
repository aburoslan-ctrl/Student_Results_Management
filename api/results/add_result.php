<?php
$method = "POST";
$cache = "no-cache";
include "../../head.php";

$user = ValidateAPITokenSentIN();
$user_id = $user->usertoken;

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
if (!is_numeric($student_id) || !is_numeric($subject_id))  {respondBadRequest("Invalid ID.");
}
elseif (!is_numeric($ca1) || !is_numeric($ca2) || !is_numeric($exam)) {respondBadRequest("Scores must be numeric.");
}elseif ($ca1 < 0 || $ca1 > 20 || $ca2 < 0 || $ca2 > 20 || $exam < 0 || $exam > 60) {
    respondBadRequest("Scores out of valid range.");
}elseif (!in_array($term, ['First', 'Second', 'Third'])) {
    respondBadRequest("Invalid term value.");
}elseif ($student_id <= 0 || $subject_id <= 0) {
    respondBadRequest("IDs must be positive integers.");
}
elseif ($user_id !== 'admin' && $user_id !== 'teacher') {
     respondUnauthorized("You are not authorized to add results.");
 } 

// Calculate total
$total = $ca1 + $ca2 + $exam;
// check if its numberic
if (!is_numeric($total)) {
    respondBadRequest("Total score calculation error.");
}

// Simple grading
if ($total >= 70) $grade = "A";
elseif ($total >= 60) $grade = "B";
elseif ($total >= 50) $grade = "C";
elseif ($total >= 45) $grade = "D";
elseif ($total >= 40) $grade = "E";
else $grade = "F";


//check duplicate result for same student, subject and term
$check = $connect->prepare("SELECT id FROM results WHERE student_id = ? AND subject_id = ? AND term = ?");
$check->bind_param("iis", $student_id, $subject_id, $term);
$check->execute();
$check_result = $check->get_result();

if ($check_result->num_rows > 0) {
    respondBadRequest("Result for this student, subject, and term already exists.");
}

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