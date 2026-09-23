<?php

function e(?string $value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect($path): never {
    header('Location: ' . $path);
    exit;
}

function flash_set($key, $message) {
    $_SESSION['flash'][$key] = $message;
}

function flash_get($key) {
    if (!isset($_SESSION['flash'][$key])) {
        return null;
    }
    $message = $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);
    return $message;
}

function deny(): never {
    http_response_code(403);
    echo '403 Forbidden';
    exit;
}

function csrf_token() {
    if (!isset($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field() {
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_verify($token) {
    if (isset($_SESSION['csrf']) && is_string($token) && hash_equals($_SESSION['csrf'], $token)) {
        return true;
    }
    return false;
}

function local_path($path, $fallback) {
    if (!is_string($path) || $path === '' || $path[0] !== '/') {
        return $fallback;
    }
    if (isset($path[1]) && ($path[1] === '/' || $path[1] === "\\")) {
        return $fallback;
    }
    return $path;
}
