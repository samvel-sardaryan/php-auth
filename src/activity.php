<?php

require_once __DIR__ . '/db.php';

function log_activity($action, $targetType = null, $targetId = null) {
    $userId = $_SESSION['user_id'] ?? null;
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;

    $stmt = db()->prepare("INSERT INTO activity_log (user_id, action, target_type, target_id, ip_address) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $action, $targetType, $targetId, $ip]);
}

function list_activity($userId = null, $limit = null, $offset = null) {
    $sql = "SELECT a.*, u.name AS user_name FROM activity_log a LEFT JOIN users u ON a.user_id = u.id ";
    $params = [];
    if ($userId) {
        $sql .= " WHERE a.user_id = ?";
        $params[] = $userId;
    }
    $sql .= " ORDER BY a.created_at DESC, a.id DESC";
    if ($limit) {
        $limit = max(1, (int) $limit);
        $sql .= ' LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset ?? 0;
    }
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function count_activity($userId) {
    $sql = "SELECT COUNT(*) FROM activity_log a LEFT JOIN users u ON a.user_id = u.id ";
    $params = [];
    if ($userId) {
        $sql .= " WHERE a.user_id = ?";
        $params[] = $userId;
    }
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}
