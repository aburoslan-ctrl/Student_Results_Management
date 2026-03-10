<?php
$method = "GET";
$cache  = "no-cache";
include "../../head.php";

// Validate token once
$datasentin = ValidateAPITokenSentIN();
$user_id = $datasentin->usertoken;

// Validate user ID
if (!isset($user_id) || input_is_invalid($user_id) || !is_numeric($user_id)) {
    respondUnauthorized();
}
$user_id = (int)$user_id;


// Prepare query
$stmt = $connect->prepare("SELECT 
        s.id,
        s.subject_name
    FROM subjects s
    ORDER BY s.id DESC
");
$stmt->execute();
$result = $stmt->get_result();
// Process results
if ($result->num_rows > 0) {

    $subjects = [];

    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row;
    }

    respondOK([
        "subjects" => $subjects,
        "total"    => count($subjects)
    ], "Subjects fetched successfully.");

} else {

    respondOK([
        "subjects" => [],
        "total"    => 0
    ], "No subjects found.");

}

// Close statement
$stmt->close();
?>