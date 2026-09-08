<?php

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

function current_permissions()
{
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

function can($permission)
{
    return in_array($permission, current_permissions(), true);
}

function has_role($roleName)
{
    $user = current_user();
    if (!$user) return false;

    return $user['role_name'] === $roleName;
}

function require_permission($permission)
{
    require_verified();
    if (!can($permission)) {
        deny();
    }
}

function require_role($roleName)
{
    require_verified();
    if (!has_role($roleName)) {
        deny();
    }
}
