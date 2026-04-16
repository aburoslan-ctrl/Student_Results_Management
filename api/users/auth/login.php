<?php
$method = "POST";
$cache  = "no-cache";
include "../../../head.php";

if (!isset($_POST['email'], $_POST['password'])) {
    respondBadRequest("Email and password required.");
}

$email    = cleanme($_POST['email']);
$password = cleanme($_POST['password']);

if (input_is_invalid($email) || input_is_invalid($password)) {
    respondBadRequest("All fields required.");
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respondBadRequest("Invalid email format.");
} elseif (strlen($password) < 6) {
    respondBadRequest("Password must be at least 6 characters.");
} elseif (!preg_match('/[A-Z]/', $password) ||
    !preg_match('/[a-z]/', $password) ||
    !preg_match('/[0-9]/', $password)) {
    respondBadRequest("Password must contain uppercase, lowercase and number.");
}

$stmt = $connect->prepare("SELECT id, password, role FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    respondBadRequest("Invalid login credentials.");
}

$user = $result->fetch_assoc();

if ($user['role'] === 'admin') {
    respondForbiddenAuthorized("Access denied. Use admin login.");
}

if (!password_verify($password, $user['password'])) {
    $stmt->close();
    respondBadRequest("Invalid login credentials.");
}

if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
    $newHash = password_hash($password, PASSWORD_DEFAULT);
    $update = $connect->prepare("UPDATE users SET password = ? WHERE id = ?");
    $update->bind_param("si", $newHash, $user['id']);
    $update->execute();
    $update->close();
}

$token = getTokenToSendAPI($user['id']);
$stmt->close();

respondOK([
    "access_token" => $token,
    "user" => [
        "id"   => $user['id'],
        "role" => $user['role']
    ]
], "Login successful.");
?>
