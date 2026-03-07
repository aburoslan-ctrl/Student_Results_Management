<?php
$method="GET";
include "../../head.php";


if(!isset($_GET['student_id'])){
    respondBadRequest("Student ID required.");
}

$datasentin = ValidateAPITokenSentIN();
$user_id = $datasentin->usertoken;

$id = cleanme($_GET['student_id']);

$sql = "SELECT subjects.subject_name, term,  ca_score1, ca_score2, exam_score, total, grade
        FROM results
        JOIN subjects ON results.subject_id = subjects.id
        WHERE student_id = ?";

$stmt = $connect->prepare($sql);
$stmt->bind_param("i",$id);
$stmt->execute();

$result = $stmt->get_result();
$data=[];

while($row=$result->fetch_assoc()){
    $data[]=$row;
}

respondOK($data,"Results fetched.");
?>