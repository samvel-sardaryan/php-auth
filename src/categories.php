<?php

require_once __DIR__ . '/db.php';

function all_categories() {
    $db = db();
    $stmt = $db->prepare("SELECT * FROM categories ORDER BY name ASC");
    $stmt->execute();
    return $stmt->fetchAll();
}

function find_category($id) {
    $db = db();
    $stmt = $db->prepare("SELECT * FROM categories WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function category_exists($id) {
    $db = db();
    $stmt = $db->prepare("SELECT id FROM categories WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    return $stmt->fetchColumn() !== false;
}

function default_category_id() {
    $db = db();
    $stmt = $db->prepare("SELECT id FROM categories WHERE is_default = 1 LIMIT 1");
    $stmt->execute();
    return $stmt->fetchColumn();
}

function create_category($name, $slug) {
    $db = db();
    $stmt = $db->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
    $stmt->execute([$name, $slug]);
}

function rename_category($id, $name, $slug) {
    $db = db();
    $stmt = $db->prepare("UPDATE categories SET name = ?, slug = ? WHERE id = ?");
    $stmt->execute([$name, $slug, $id]);
}

function delete_category($id) {
    $db = db();
    $db->prepare("UPDATE posts SET category_id = ? WHERE category_id = ?")->execute([default_category_id(), $id]);
    $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$id]);
}

function slugify($name) {
    $string = strtolower($name);
    $string = preg_replace('/[^a-z0-9]+/', '-', $string);
    return $string = trim($string, '-');
}

function category_post_counts() {
    $stmt = db()->prepare("SELECT category_id, COUNT(*) AS total FROM posts WHERE deleted_at IS NULL GROUP BY category_id");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
}
