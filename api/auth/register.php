<?php
$method = "POST";
$cache  = "no-cache";
include "../../head.php";


if (!isset($_POST['username'], $_POST['email'], $_POST['password'])) {
    respondBadRequest("All fields are required.");
}

$username = trim(cleanme($_POST['username']));
$email    = trim(cleanme($_POST['email']));
$password = trim(cleanme($_POST['password']));

/*  VALIDATION */
if (empty($username) || empty($email) || empty($password)) {
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

/*  CHECK DUPLICATE */
$check = $connect->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
$check->bind_param("ss", $email, $username);
$check->execute();

if ($check->get_result()->num_rows > 0) {
    respondBadRequest("Account already exists.");
}

$role   = "teacher";
$stmt = $connect->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $username, $email, $password, $role);

if ($stmt->execute()) {
    
respondOK([
    "access_token" => $token
], "Registration successful.");
    
}else{
    respondBadRequest("Registration failed.");
}




?>