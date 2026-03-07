<?php
$method="POST";
include "../../head.php";
$user = ValidateAPITokenSentIN();

if(!isset($_POST['result_id'])){
    respondBadRequest("Result ID required.");
}

$id = cleanme($_POST['result_id']);

$stmt = $connect->prepare("DELETE FROM results WHERE id=?");
$stmt->bind_param("i",$id);

if($stmt->execute()){
    respondOK([],"Result deleted.");
}else{
    respondBadRequest("Delete failed.");
}
?>