<?php
$method = "GET";
$cache  = "no-cache";
include "../../head.php";

// ✅ Validate token once
$datasentin = ValidateAPITokenSentIN();
$user_id = $datasentin->usertoken;

// Validate user ID
if (!isset($user_id) || input_is_invalid($user_id) || !is_numeric($user_id)) {
    respondUnauthorized();
}
$user_id = (int)$user_id;

// ✅ Prepare query
$stmt = $connect->prepare("
    SELECT 
        c.id,
        c.class_name
    FROM classes c
    ORDER BY c.id DESC
");

$stmt->execute();
$result = $stmt->get_result();

// ✅ Process results
if ($result->num_rows > 0) {

    $classes = [];

    while ($row = $result->fetch_assoc()) {
        $classes[] = $row;
    }

    respondOK([
        "classes" => $classes,
        "total"   => count($classes)
    ], "Classes fetched successfully.");

} else {

    respondOK([
        "classes" => [],
        "total"   => 0
    ], "No classes found.");

}

// Close statement
$stmt->close();
?>