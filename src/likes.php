<?php

require_once __DIR__ . '/db.php';

function toggle_like($userId, $postId) {
    try {
        $stmt = db()->prepare("INSERT INTO post_likes (user_id, post_id) VALUES (?, ?)");
        $stmt->execute([$userId, $postId]);
    } catch (PDOException $e) {
        $errorCode = $e->getCode();
        if ($errorCode !== '23000') {
            throw $e;
        }
        $stmt = db()->prepare("DELETE FROM post_likes WHERE user_id = ? AND post_id = ?");
        $stmt->execute([$userId, $postId]);
        return 'unliked';
    }
    return 'liked';
}

function liked_by(array $postIds, int $userId) {
    if (empty($postIds)) {
        return [];
    }

    $placeholders = rtrim(str_repeat('?,', count($postIds)), ',');
    $stmt = db()->prepare("SELECT post_id FROM post_likes WHERE post_id IN ($placeholders) AND user_id = ?");
    $stmt->execute([...$postIds, $userId]);
    $likes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    return $likes;
}
