<?php

require_once __DIR__ . '/db.php';

function all_roles() {
    $db = db();
    $stmt = $db->prepare("SELECT id, name FROM roles ORDER BY id");
    $stmt->execute();
    return $stmt->fetchAll();
}

function role_exists($roleId) {
    $db = db();
    $stmt = $db->prepare("SELECT id FROM roles WHERE id = ? LIMIT 1");
    $stmt->execute([$roleId]);
    return $stmt->fetchColumn() !== false;
}

function assign_role($userId, $roleId) {
    $db = db();
    $stmt = $db->prepare("UPDATE users SET role_id = ? WHERE id = ?");
    $stmt->execute([$roleId, $userId]);
}
