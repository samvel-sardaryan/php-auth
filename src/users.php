<?php

require_once __DIR__ . '/db.php';

function user_exists($userId) {
    $db = db();
    $stmt = $db->prepare("SELECT id FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$userId]);
    return $stmt->fetchColumn() !== false;
}

function list_users() {
    $db = db();
    $stmt = $db->prepare("SELECT u.id, u.name, u.email, u.role_id, r.name as role_name, u.email_verified_at, u.created_at
        FROM users u LEFT JOIN roles r ON u.role_id = r.id ORDER BY u.id");
    $stmt->execute();
    return $stmt->fetchAll();
}
