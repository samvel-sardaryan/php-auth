<?php

require_once __DIR__ . '/db.php';

function create_comment($postId, $userId, $content, $parentId = null) {
    $db = db();
    $stmt = $db->prepare("INSERT INTO comments (post_id, user_id, content, parent_id) "
        . " VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $postId,
        $userId,
        $content,
        $parentId
    ]);
    return (int) $db->lastInsertId();
}

function find_comment($id) {
    $db = db();
    $stmt = $db->prepare("SELECT comments.*, users.name as author_name FROM comments JOIN users ON comments.user_id = users.id WHERE comments.id = ? AND comments.deleted_at IS NULL");
    $stmt->execute([$id]);
    $comment = $stmt->fetch();
    return $comment ?: null;
}

function comments_for_post($postId) {
    $db = db();
    $stmt = $db->prepare("SELECT comments.*, users.name as author_name FROM comments JOIN users ON comments.user_id = users.id WHERE comments.post_id = ? AND comments.parent_id IS NULL AND (comments.deleted_at IS NULL OR EXISTS (SELECT 1 FROM comments AS replies WHERE replies.parent_id = comments.id AND replies.deleted_at IS NULL)) ORDER BY comments.created_at ASC, comments.id ASC");
    $stmt->execute([$postId]);
    $comments['top'] = $stmt->fetchAll() ?: [];
    $top = array_column($comments['top'], 'id');

    if (!empty($top)) {
        $placeholders = rtrim(str_repeat('?,', count($top)), ',');
        $stmt = $db->prepare("SELECT comments.*, users.name as author_name FROM comments JOIN users ON comments.user_id = users.id WHERE comments.parent_id IN (" . $placeholders . ") AND comments.deleted_at IS NULL ORDER BY comments.created_at ASC, comments.id ASC");
        $stmt->execute($top);
        $comments['replies'] = $stmt->fetchAll() ?: [];
    } else {
        $comments['replies'] = [];
    }

    $replies = [];
    foreach ($comments['replies'] as $reply) {
        $replies[$reply['parent_id']][] = $reply;
    }

    $comments['replies'] = $replies;
    return $comments;
}

function update_comment($id, $content) {
    $db = db();
    $stmt = $db->prepare("UPDATE comments SET content = ? WHERE id = ? AND deleted_at IS NULL");
    $stmt->execute([$content, $id]);
    return $stmt->rowCount();
}

function delete_comment($id) {
    $db = db();
    $stmt = $db->prepare("UPDATE comments SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL");
    $stmt->execute([$id]);
    return $stmt->rowCount();
}
