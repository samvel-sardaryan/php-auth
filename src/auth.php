<?php

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/mail.php';

function email_exists($email) {
    $db = db();
    $stmt = $db->prepare("SELECT email FROM users WHERE email = ? LIMIT 1;");
    $stmt->execute([$email]);

    return $stmt->fetch() !== false;
}

function create_user($name, $email, $password) {
    $db = db();
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO users (name, email, password_hash) VALUES (?,?,?);");
    $stmt->execute([$name, $email, $hash]);

    return (int) db()->lastInsertId();
}

function attempt_login($email, $password) {
    $db = db();
    $stmt = $db->prepare("SELECT id, password_hash FROM users WHERE email = ? LIMIT 1;");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        return (int) $user['id'];
    }

    return null;
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function current_user() {
    if (!is_logged_in()) {
        return null;
    }

    $db = db();
    $stmt = $db->prepare("SELECT u.id, u.name, u.email, u.created_at, u.email_verified_at, u.role_id, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ? LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);

    return $stmt->fetch();
}

function login_user($id) {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $id;
}

function logout_user() {
    $_SESSION = [];
    session_destroy();
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 3600, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

function require_login() {
    if (!is_logged_in()) {
        redirect('/login');
    }
}

function require_guest() {
    if (is_logged_in()) {
        redirect('/dashboard');
    }
}

function generate_token() {
    return bin2hex(random_bytes(32));
}

function set_verification_token($userId) {
    $token = generate_token();
    $hashedToken = hash('sha256', $token);

    $db = db();
    $stmt = $db->prepare("UPDATE users SET verification_token = ?, verification_expires_at = DATE_ADD(NOW(), INTERVAL ? MINUTE) WHERE id = ?;");
    $stmt->execute([$hashedToken, VERIFY_TOKEN_TTL, $userId]);

    return $token;
}

function send_verification_email($email, $token) {
    $link = APP_URL . '/verify-email?token=' . $token;
    $subject = "Verify your email";
    $body = "Please click the link to verify your email: " . $link;

    return send_mail($email, $subject, $body);
}

function send_reset_password_email($email, $token) {
    $link = APP_URL . '/reset-password?token=' . $token;
    $subject = "Reset your password";
    $body = "Please click the link to reset your password: " . $link;

    return send_mail($email, $subject, $body);
}

function verify_email_token($token) {
    $hashedToken = hash('sha256', $token);

    $db = db();
    $stmt = $db->prepare("SELECT id FROM users WHERE verification_token = ? AND verification_expires_at > NOW() AND email_verified_at IS NULL LIMIT 1");
    $stmt->execute([$hashedToken]);
    $user = $stmt->fetch();

    if ($user) {
        $stmt = $db->prepare("UPDATE users SET email_verified_at = NOW(), verification_token = NULL, verification_expires_at = NULL WHERE id = ?");
        $stmt->execute([$user['id']]);
        return true;
    }

    return false;
}

function is_verified($user) {
    return $user && $user['email_verified_at'] !== null;
}

function require_verified() {
    require_login();

    if (!is_verified(current_user())) {
        redirect('/verify-notice');
    }
}

function set_reset_token($email) {
    $db = db();
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1;");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        return null;
    }

    $token = generate_token();
    $hashedToken = hash('sha256', $token);

    $stmt = $db->prepare("UPDATE users SET reset_token = ?, reset_expires_at = DATE_ADD(NOW(), INTERVAL ? MINUTE) WHERE id = ?");
    $stmt->execute([$hashedToken, RESET_TOKEN_TTL, $user['id']]);

    return $token;
}

function find_user_by_reset_token($token) {
    $hashedToken = hash('sha256', $token);

    $db = db();
    $stmt = $db->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires_at > NOW() LIMIT 1");
    $stmt->execute([$hashedToken]);
    $user = $stmt->fetch();

    if ($user) {
        return (int) $user['id'];
    }

    return null;
}

function reset_user_password($userId, $password) {
    $db = db();
    $stmt = $db->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_expires_at = NULL WHERE id = ?");
    $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $userId]);

    return true;
}

function email_exists_for_other($email, $userId) {
    $db = db();
    $stmt = $db->prepare("SELECT email FROM users WHERE email = ? AND id != ? LIMIT 1;");
    $stmt->execute([$email, $userId]);

    return $stmt->fetch() !== false;
}

function update_profile($userId, $name, $email) {
    $db = db();
    $stmt = $db->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?;");
    return $stmt->execute([$name, $email, $userId]);
}
