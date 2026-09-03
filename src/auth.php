<?php

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

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
    $stmt = $db->prepare("SELECT id, name, email, created_at FROM users WHERE id = ? LIMIT 1");
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
}

function require_login() {
    if (!is_logged_in()) {
        redirect('/login');
    }
}