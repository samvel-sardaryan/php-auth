<?php

const AVATAR_UPLOAD_PATH = __DIR__ . "/../public/uploads/avatars/";
const AVATAR_MAX_SIZE = 2000000;

const AVATAR_TYPES = [
    IMAGETYPE_JPEG => ".jpg",
    IMAGETYPE_PNG  => ".png",
    IMAGETYPE_GIF  => ".gif",
    IMAGETYPE_WEBP => ".webp",
];

function store_avatar(array $file, &$error = null) {
    if (empty($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        $error = "File not selected.";
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = "Upload failed.";
        return null;
    }

    if ($file['size'] > AVATAR_MAX_SIZE) {
        $error = "File size must be less than 2MB.";
        return null;
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        $error = "Upload failed.";
        return null;
    }

    $info = @getimagesize($file['tmp_name']);
    if (!$info) {
        $error = "File is not an image.";
        return null;
    }

    if (!isset(AVATAR_TYPES[$info[2]])) {
        $error = "Invalid image type.";
        return null;
    }

    $filename = bin2hex(random_bytes(16)) . AVATAR_TYPES[$info[2]];
    if (!move_uploaded_file($file['tmp_name'], AVATAR_UPLOAD_PATH . $filename)) {
        $error = "Could not save the image.";
        return null;
    }

    return $filename;
}

function delete_avatar_file($filename) {
    if (empty($filename)) {
        return;
    }

    $path = AVATAR_UPLOAD_PATH . basename($filename);
    if (file_exists($path) && is_file($path)) {
        @unlink($path);
    }
}
