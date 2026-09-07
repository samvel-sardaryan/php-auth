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