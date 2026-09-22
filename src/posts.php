<?php

require_once __DIR__ . '/db.php';

const STATUS_TYPES = ['draft', 'published', 'archived'];

const POST_SELECT = "SELECT posts.*, users.name AS author_name, categories.name AS category_name
    FROM posts
    JOIN users ON users.id = posts.user_id
    JOIN categories ON categories.id = posts.category_id";

function post_visibility($viewerId = null, $seeAll = false) {
    $live = 'posts.deleted_at IS NULL';

    if ($seeAll) {
        return [$live, []];
    }

    if ($viewerId === null) {
        return ["$live AND posts.status = 'published'", []];
    }

    return ["$live AND (posts.status = 'published' OR posts.user_id = ?)", [$viewerId]];
}

function create_post($userId, $title, $content, $status, $categoryId) {
    $stmt = db()->prepare("INSERT INTO posts (user_id, title, content, status, category_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $title, $content, $status, $categoryId]);
    return (int) db()->lastInsertId();
}

function find_post($id, $viewerId = null, $seeAll = false) {
    [$where, $params] = post_visibility($viewerId, $seeAll);
    $stmt = db()->prepare(POST_SELECT . " WHERE posts.id = ? AND $where LIMIT 1");
    $stmt->execute([$id, ...$params]);
    return $stmt->fetch();
}

function list_posts($viewerId = null, $seeAll = false) {
    [$where, $params] = post_visibility($viewerId, $seeAll);
    $stmt = db()->prepare(POST_SELECT . " WHERE $where ORDER BY posts.created_at DESC, posts.id DESC");
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function list_user_posts($userId, $viewerId = null, $seeAll = false) {
    [$where, $params] = post_visibility($viewerId, $seeAll);
    $stmt = db()->prepare(POST_SELECT . " WHERE posts.user_id = ? AND $where ORDER BY posts.created_at DESC, posts.id DESC");
    $stmt->execute([$userId, ...$params]);
    return $stmt->fetchAll();
}

function update_post($id, $title, $content, $status, $categoryId) {
    $stmt = db()->prepare("UPDATE posts SET title = ?, content = ?, status = ?, category_id = ? WHERE id = ? AND deleted_at IS NULL");
    return $stmt->execute([$title, $content, $status, $categoryId, $id]);
}

function delete_post($id) {
    $stmt = db()->prepare("UPDATE posts SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL");
    $stmt->execute([$id]);
    return $stmt->rowCount();
}

function add_post_images($postId, array $files) {
    $db = db();
    $stmt = $db->prepare("INSERT INTO post_images (post_id, path) VALUES (?, ?)");
    foreach ($files as $file) {
        $stmt->execute([$postId, $file]);
    }
}

function images_for_post($postId) {
    $stmt = db()->prepare("SELECT id, path FROM post_images WHERE post_id = ? ORDER BY id");
    $stmt->execute([$postId]);
    return $stmt->fetchAll();
}

function images_for_posts(array $postIds) {
    if (empty($postIds)) {
        return [];
    }

    $placeholders = rtrim(str_repeat('?,', count($postIds)), ',');
    $stmt = db()->prepare("SELECT post_id, path FROM post_images WHERE post_id IN ($placeholders) ORDER BY id");
    $stmt->execute($postIds);

    $grouped = [];
    while ($row = $stmt->fetch()) {
        $grouped[$row['post_id']][] = $row['path'];
    }

    return $grouped;
}

function delete_post_images($postId, array $imageIds) {
    if (empty($imageIds)) {
        return;
    }

    $placeholders = rtrim(str_repeat('?,', count($imageIds)), ',');
    $stmt = db()->prepare("DELETE FROM post_images WHERE post_id = ? AND id IN ($placeholders)");
    $stmt->execute([$postId, ...$imageIds]);
}
