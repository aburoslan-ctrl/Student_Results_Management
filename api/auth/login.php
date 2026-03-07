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

$token = getTokenToSendAPI($user['id']);
respondOK(["access_token" => $token], "Login successful.");
?>