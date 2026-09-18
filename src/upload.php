<?php

const AVATAR_UPLOAD_PATH = __DIR__ . "/../public/uploads/avatars/";
const AVATAR_MAX_SIZE = 2000000;

const POST_IMAGE_UPLOAD_PATH = __DIR__ . "/../public/uploads/posts/";
const POST_IMAGE_MAX_SIZE = 2000000;
const POST_IMAGE_MAX_COUNT = 5;

const IMAGE_TYPES = [
    IMAGETYPE_JPEG => ".jpg",
    IMAGETYPE_PNG  => ".png",
    IMAGETYPE_GIF  => ".gif",
    IMAGETYPE_WEBP => ".webp",
];

function store_uploaded_image(array $file, string $dir, int $maxBytes, &$error = null) {
    if (empty($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        $error = "File not selected.";
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = "Upload failed.";
        return null;
    }

    if ($file['size'] > $maxBytes) {
        $error = "File size must be less than " . round($maxBytes / 1000000) . "MB.";
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

    if (!isset(IMAGE_TYPES[$info[2]])) {
        $error = "Invalid image type.";
        return null;
    }

    $filename = bin2hex(random_bytes(16)) . IMAGE_TYPES[$info[2]];
    if (!move_uploaded_file($file['tmp_name'], $dir . $filename)) {
        $error = "Could not save the image.";
        return null;
    }

    return $filename;
}

function delete_uploaded_image($filename, string $dir) {
    if (empty($filename)) {
        return;
    }

    $path = $dir . basename($filename);
    if (is_file($path)) {
        @unlink($path);
    }
}

function store_avatar(array $file, &$error = null) {
    return store_uploaded_image($file, AVATAR_UPLOAD_PATH, AVATAR_MAX_SIZE, $error);
}

function delete_avatar_file($filename) {
    delete_uploaded_image($filename, AVATAR_UPLOAD_PATH);
}

function store_post_image(array $file, &$error = null) {
    return store_uploaded_image($file, POST_IMAGE_UPLOAD_PATH, POST_IMAGE_MAX_SIZE, $error);
}

function delete_post_image_file($filename) {
    delete_uploaded_image($filename, POST_IMAGE_UPLOAD_PATH);
}
