<?php

require_once __DIR__ . '/db.php';

function too_many_posts($userId) {
    $stmt = db()->prepare("SELECT COUNT(*) FROM posts WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    $stmt->execute([$userId]);
    $count = $stmt->fetchColumn();
    return $count >= 10;
}

function too_many_comments($userId) {
    $stmt = db()->prepare("SELECT COUNT(*) FROM comments WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)");
    $stmt->execute([$userId]);
    $count = $stmt->fetchColumn();
    return $count >= 5;
}

function record_failed_login($email, $ip) {
    $stmt = db()->prepare("INSERT INTO login_attempts (email, ip_address) VALUES (?, ?)");
    $stmt->execute([$email, $ip]);
}

function too_many_login_attempts($email, $ip) {
    $stmt = db()->prepare("SELECT COUNT(*) FROM login_attempts WHERE (email = ? OR ip_address = ?) AND created_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
    $stmt->execute([$email, $ip]);
    $count = $stmt->fetchColumn();
    return $count >= 5;
}

function clear_login_attempts($email) {
    $stmt = db()->prepare("DELETE FROM login_attempts WHERE email = ?");
    $stmt->execute([$email]);
}
