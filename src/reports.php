<?php

require_once __DIR__ . '/db.php';

function create_report($reporterId, $targetType, $targetId, $reason) {
    $sql = "INSERT INTO reports (reporter_id, target_type, target_id, reason) VALUES (?, ?, ?, ?)";
    $stmt = db()->prepare($sql);
    return $stmt->execute([
        $reporterId,
        $targetType,
        $targetId,
        $reason
    ]);
}

function list_reports() {
    $sql = "SELECT r.*, u.name AS reporter_name
            FROM reports r 
            JOIN users u ON r.reporter_id = u.id
            ORDER BY r.created_at DESC, r.id DESC";
    $stmt = db()->prepare($sql);
    $stmt->execute();
    $reports = $stmt->fetchAll();

    if (empty($reports)) {
        return [];
    }

    $posts = [];
    $comments = [];
    foreach ($reports as $report) {
        if ($report['target_type'] === 'post') {
            $posts[] = $report['target_id'];
        } elseif ($report['target_type'] === 'comment') {
            $comments[] = $report['target_id'];
        }
    }
    $posts = array_unique($posts);
    $comments = array_unique($comments);

    if ($posts) {
        $postIds = rtrim(str_repeat('?,', count($posts)), ',');
        $stmt = db()->prepare("SELECT p.id, p.title, p.deleted_at, u.name AS author_name FROM posts p JOIN users u ON p.user_id = u.id WHERE p.id IN ($postIds)");
        $stmt->execute($posts);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $postsFinal = [];
    foreach ($posts as $post) {
        $postsFinal[$post['id']] = $post;
    }

    if ($comments) {
        $commentIds = rtrim(str_repeat('?,', count($comments)), ',');
        $stmt = db()->prepare("SELECT c.id, c.content, c.deleted_at, c.post_id, u.name AS author_name FROM comments c JOIN users u ON c.user_id = u.id WHERE c.id IN ($commentIds)");
        $stmt->execute($comments);
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $commentsFinal = [];
    foreach ($comments as $comment) {
        $commentsFinal[$comment['id']] = $comment;
    }

    foreach ($reports as &$report) {
        if ($report['target_type'] === 'post') {
            $report['target'] = $postsFinal[$report['target_id']] ?? null;
        } elseif ($report['target_type'] === 'comment') {
            $report['target'] = $commentsFinal[$report['target_id']] ?? null;
        }
    }
    return $reports;
}
