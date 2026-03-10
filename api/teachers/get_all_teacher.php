<?php
$method = "GET";
$cache  = "no-cache";
include "../../head.php";

// Validate API token
$user = ValidateAPITokenSentIN();

// Check if a specific teacher ID is requested
$teacher_id = isset($_GET['id']) ? (int)cleanme($_GET['id']) : null;

if ($teacher_id) {
    // Fetch a single teacher
    $stmt = $connect->prepare("SELECT id, username, email, fullname, role, created_at, updated_at FROM teachers WHERE id = ?");
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        respondBadRequest("Teacher not found.");
    }

    $teacher = $result->fetch_assoc();
    respondOK($teacher, "Teacher retrieved successfully.");
} else {
    // Fetch all teachers
    $stmt = $connect->prepare("SELECT id, username, email, fullname, role, created_at, updated_at FROM teachers ORDER BY created_at DESC");
    $stmt->execute();
    $result = $stmt->get_result();

    $teachers = [];
    while ($row = $result->fetch_assoc()) {
        $teachers[] = $row;
    }

    respondOK($teachers, "Teachers retrieved successfully.");
}
?>