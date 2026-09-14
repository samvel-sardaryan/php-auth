<?php

require_once __DIR__ . '/db.php';

function create_post($userId, $title, $content) {
    $db = db();
    $stmt = $db->prepare("INSERT INTO posts (user_id, title, content) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $title, $content]);
    return (int) $db->lastInsertId();
}

function find_post($id) {
    $db = db();
    $stmt = $db->prepare("SELECT posts.*, users.name AS author_name FROM posts JOIN users ON posts.user_id = users.id WHERE posts.id = ? AND posts.deleted_at IS NULL LIMIT 1");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function list_posts() {
    $db = db();
    $stmt = $db->prepare("SELECT posts.*, users.name AS author_name FROM posts JOIN users ON posts.user_id = users.id WHERE posts.deleted_at IS NULL ORDER BY posts.created_at DESC, posts.id DESC");
    $stmt->execute();
    return $stmt->fetchAll();
}

function list_user_posts($userId) {
    $db = db();
    $stmt = $db->prepare("SELECT posts.*, users.name AS author_name FROM posts JOIN users ON posts.user_id = users.id WHERE users.id = ? AND posts.deleted_at IS NULL ORDER BY posts.created_at DESC, posts.id DESC");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function update_post($id, $title, $content) {
    $db = db();
    $stmt = $db->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ? AND deleted_at IS NULL");
    return $stmt->execute([$title, $content, $id]);
}

function delete_post($id) {
    $db = db();
    $stmt = $db->prepare("UPDATE posts SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL");
    $stmt->execute([$id]);
    return $stmt->rowCount();
}
