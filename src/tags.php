<?php

require_once __DIR__ . '/db.php';

function normalise_tags($raw) {
    $tag = explode(',', $raw);
    $tag = array_map('trim', $tag);
    $tag = array_map('mb_strtolower', $tag);
    $tag = array_filter($tag);
    $tag = array_map(function ($tag) {
        return mb_substr($tag, 0, 30);
    }, $tag);
    $tag = array_unique($tag);
    return array_slice(array_values($tag), 0, 5);
}

function find_or_create_tags(array $names) {
    if (empty($names)) return [];

    $db = db();
    $placeholder = rtrim(str_repeat('?,', count($names)), ',');
    $stmt = $db->prepare("
        SELECT id, name
        FROM tags
        WHERE name IN ($placeholder)
    ");
    $stmt->execute($names);
    $rows = $stmt->fetchAll();

    $exists = [];

    foreach ($rows as $row) {
        $exists[$row['name']] = $row['id'];
    }

    $new_names = array_values(array_diff($names, array_keys($exists)));

    if (!empty($new_names)) {
        $placeholders = rtrim(str_repeat('(?),', count($new_names)), ',');
        $stmt = $db->prepare("INSERT INTO tags (name) VALUES $placeholders");
        $stmt->execute($new_names);

        $stmt = $db->prepare("SELECT id, name
        FROM tags
        WHERE name IN ($placeholders)");
        $stmt->execute($new_names);
        $rows = $stmt->fetchAll();

        foreach ($rows as $row) {
            $exists[$row['name']] = $row['id'];
        }
    }

    $orderedIds = [];
    foreach ($names as $name) {
        if (isset($exists[$name])) {
            $orderedIds[] = $exists[$name];
        }
    }

    return $orderedIds;
}

function set_post_tags($postId, array $tagIds) {
    $db = db();
    $stmt = $db->prepare("DELETE FROM post_tag WHERE post_id = ?");
    $stmt->execute([$postId]);

    foreach ($tagIds as $tagId) {
        $stmt = $db->prepare("INSERT INTO post_tag (post_id, tag_id) VALUES (?, ?)");
        $stmt->execute([$postId, $tagId]);
    }
}

function tags_for_post($postId) {
    $db = db();
    $stmt = $db->prepare("
        SELECT t.name
        FROM post_tag pt
        JOIN tags t ON pt.tag_id = t.id
        WHERE pt.post_id = ?
        ORDER BY t.name
    ");
    $stmt->execute([$postId]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function tags_for_posts(array $postIds) {
    if (empty($postIds)) return [];

    $db = db();
    $placeholder = rtrim(str_repeat('?,', count($postIds)), ',');
    $stmt = $db->prepare("
        SELECT t.name, pt.post_id
        FROM post_tag pt
        JOIN tags t ON pt.tag_id = t.id
        WHERE pt.post_id IN ($placeholder)
        ORDER BY t.name
    ");
    $stmt->execute($postIds);
    $rows = $stmt->fetchAll();

    $out = [];
    foreach ($rows as $row) {
        if (!isset($out[$row['post_id']])) {
            $out[$row['post_id']] = [];
        }
        $out[$row['post_id']][] = $row['name'];
    }

    foreach ($postIds as $postId) {
        if (!isset($out[$postId])) {
            $out[$postId] = [];
        }
    }

    return $out;
}
