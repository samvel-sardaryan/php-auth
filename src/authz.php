<?php

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

function current_permissions() {
    static $perms = null;
    if ($perms !== null) return $perms;

    $user = current_user();
    if (!$user) return $perms = [];

    $db = db();
    $stmt = $db->prepare("SELECT p.name FROM role_permissions rp
    JOIN permissions p ON rp.permission_id = p.id
    WHERE rp.role_id = ?;");
    $stmt->execute([$user['role_id']]);
    return $perms = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function can($permission) {
    return in_array($permission, current_permissions(), true);
}

function has_role($roleName) {
    $user = current_user();
    if (!$user) return false;

    return $user['role_name'] === $roleName;
}

function require_permission($permission) {
    require_verified();
    if (!can($permission)) {
        deny();
    }
}

function owns_post($post) {
    $user = current_user();
    if (!$user) return false;
    if (!$post) return false;

    return $user['id'] === (int) $post['user_id'];
}

function require_post_owner($post) {
    require_verified();
    if (!can('manage_posts') && !owns_post($post)) {
        deny();
    }
}

function require_role($roleName) {
    require_verified();
    if (!has_role($roleName)) {
        deny();
    }
}

function owns_comment($comment) {
    $user = current_user();
    if (!$user) return false;
    if (!$comment) return false;

    return $user['id'] === (int) $comment['user_id'];
}

function require_comment_editor($comment) {
    require_verified();
    if (!owns_comment($comment)) {
        deny();
    }
}

function require_comment_deleter($comment, $post) {
    require_verified();
    if (!can('moderate_comments') && !owns_comment($comment) && !owns_post($post)) {
        deny();
    }
}
