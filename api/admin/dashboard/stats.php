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

$countFromQuery = function ($sql) use ($connect) {
    $res = $connect->query($sql);
    if (!$res) {
        respondInternalError("Query failed: " . $connect->error);
    }
    return (int)($res->fetch_assoc()['count'] ?? 0);
};

$users    = $countFromQuery("SELECT COUNT(*) as count FROM users");
$students = $countFromQuery("SELECT COUNT(*) as count FROM students");
$teachers = $countFromQuery("SELECT COUNT(*) as count FROM teachers");
$classes  = $countFromQuery("SELECT COUNT(*) as count FROM classes");
$subjects = $countFromQuery("SELECT COUNT(*) as count FROM subjects");
$results  = $countFromQuery("SELECT COUNT(*) as count FROM results");
$gradeA   = $countFromQuery("SELECT COUNT(*) as count FROM results WHERE grade = 'A'");
$gradeF   = $countFromQuery("SELECT COUNT(*) as count FROM results WHERE grade = 'F'");

respondOK([
    "total_users"    => (int)$users,
    "total_students" => (int)$students,
    "total_teachers" => (int)$teachers,
    "total_classes"  => (int)$classes,
    "total_subjects" => (int)$subjects,
    "total_results"  => (int)$results,
    "grade_a_count"  => (int)$gradeA,
    "grade_f_count"  => (int)$gradeF
], "Dashboard stats fetched successfully.");

?>
