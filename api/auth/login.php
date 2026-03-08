<?php
$method = "POST";
include "../../head.php";

if (!isset($_POST['email'], $_POST['password'])) {
    respondBadRequest("Email and password required.");
}

$email    = cleanme($_POST['email']);
$password = cleanme($_POST['password']);

if (input_is_invalid($email) || input_is_invalid($password)) {
    respondBadRequest("All fields required.");
}

$stmt = $connect->prepare("SELECT id, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    respondBadRequest("Invalid login credentials.");
}

$user = $result->fetch_assoc();

if (!password_verify($password, $user['password'])) {
    $stmt->close();
    respondBadRequest("Invalid login credentials.");
}

// If hash settings change in future, update to a new hash after successful login.
if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
    $newHash = password_hash($password, PASSWORD_DEFAULT);
    $update = $connect->prepare("UPDATE users SET password = ? WHERE id = ?");
    $update->bind_param("si", $newHash, $user['id']);
    $update->execute();
    $update->close();
}

$token = getTokenToSendAPI($user['id']);
$stmt->close();
respondOK(["access_token" => $token], "Login successful.");
?>
