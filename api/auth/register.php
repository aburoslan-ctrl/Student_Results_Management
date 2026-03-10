<?php
$method = "POST";
$cache  = "no-cache";
include "../../head.php";


if (!isset($_POST['username'], $_POST['email'], $_POST['password'],$_POST['role'])) {
    respondBadRequest("All fields are required.");
}

$username = trim(cleanme($_POST['username']));
$email    = trim(cleanme($_POST['email']));
$password = trim(cleanme($_POST['password']));
$role     = trim(cleanme($_POST['role']));

/*  VALIDATION */
if (empty($username) || empty($email) || empty($password) || empty($role)) {
    respondBadRequest("All fields are required.");
}

elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respondBadRequest("Invalid email format.");
}


elseif(strlen($password) < 6) {
    respondBadRequest("Password must be at least 6 characters.");
}

elseif(!preg_match('/[A-Z]/', $password) ||
    !preg_match('/[a-z]/', $password) ||
    !preg_match('/[0-9]/', $password)) {
    respondBadRequest("Password must contain uppercase, lowercase and number.");
}

$passwordHash = password_hash($password, PASSWORD_DEFAULT);
if ($passwordHash === false) {
    respondBadRequest("Unable to process password.");
}
// role validation
$allowed_roles = [ 'student', 'admin', 'teacher'];
if (!in_array($role, $allowed_roles)) {
    respondBadRequest("Invalid role.");
}

/*  CHECK DUPLICATE */
$check = $connect->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
$check->bind_param("ss", $email, $username);
$check->execute();

if ($check->get_result()->num_rows > 0) {
    $check->close();
    respondBadRequest("Account already exists.");
}
$check->close();

$stmt = $connect->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $username, $email, $passwordHash, $role);

if ($stmt->execute()) {
    $newUserId = $stmt->insert_id;
    $token = getTokenToSendAPI($newUserId);
    $stmt->close();
    respondOK([ ], "Registration successful.");
} else {
    $stmt->close();
    respondBadRequest("Registration failed.");
}
?>
