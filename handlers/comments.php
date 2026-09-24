<?php

// Comments and replies on a post.

function post_comment_create() {
    require_verified();

    $user = current_user();
    $post = find_post((int)($_POST['post_id'] ?? 0));
    $parentId = (int)($_POST['parent_id'] ?? 0);
    $content = trim($_POST['content'] ?? '');

    if (!$post) {
        http_response_code(404);
        echo '404 Not Found';
        exit;
    }

    if (too_many_comments($user['id'])) {
        flash_set('error', 'Too many comments. Please try again later.');
        redirect('/posts/show?id=' . $post['id']);
    }

    if ($parentId === 0) {
        $parentId = null;
    } else {
        $parentComment = find_comment($parentId);
        if (!$parentComment) {
            http_response_code(404);
            echo '404 Not Found';
            exit;
        } else if ($parentComment['post_id'] !== $post['id']) {
            http_response_code(404);
            echo '404 Not Found';
            exit;
        } else if ($parentComment['parent_id'] !== null) {
            $parentId = $parentComment['parent_id'];
        }
    }

    $errors = validate_comment($content);
    if (!empty($errors)) {
        flash_set('errors', $errors);
        flash_set('old', [
            'content' => $content,
        ]);
        redirect('/posts/show?id=' . $post['id']);
    }

    create_comment($post['id'], current_user()['id'], $content, $parentId);
    flash_set('success', 'Comment created successfully.');
    redirect('/posts/show?id=' . $post['id']);
}

function show_comment_edit() {
    require_verified();
    $comment = find_comment((int)($_GET['id'] ?? 0));

    if (!$comment) {
        http_response_code(404);
        echo '404 Not Found';
        exit;
    }

    $post = find_post($comment['post_id'], null, true);

    if (!$post) {
        http_response_code(404);
        echo '404 Not Found';
        exit;
    }

    require_comment_editor($comment);

    $errors = flash_get('errors') ?? [];
    $error = flash_get('error');
    $old = flash_get('old') ?? [
        'content' => $comment['content'],
    ];

    require __DIR__ . '/../views/comment-edit.php';
}

function post_comment_edit() {
    require_verified();
    $comment = find_comment((int)($_POST['id'] ?? 0));

    if (!$comment) {
        http_response_code(404);
        echo '404 Not Found';
        exit;
    }

    require_comment_editor($comment);

    $post = find_post($comment['post_id'], null, true);
    $content = trim($_POST['content'] ?? '');

    if (!$post) {
        http_response_code(404);
        echo '404 Not Found';
        exit;
    }

    $errors = validate_comment($content);
    if (!empty($errors)) {
        flash_set('errors', $errors);
        flash_set('old', [
            'content' => $content,
        ]);
        redirect('/comments/edit?id=' . $comment['id']);
    }

    update_comment($comment['id'], $content);
    flash_set('success', 'Comment updated successfully.');
    redirect('/posts/show?id=' . $post['id']);
}

function post_comment_delete() {
    require_verified();
    $comment = find_comment((int)($_POST['id'] ?? 0));

    if (!$comment) {
        http_response_code(404);
        echo '404 Not Found';
        exit;
    }

    $post = find_post($comment['post_id'], null, true);

    if (!$post) {
        http_response_code(404);
        echo '404 Not Found';
        exit;
    }

    require_comment_deleter($comment, $post);

    delete_comment($comment['id']);
    flash_set('success', 'Comment deleted successfully.');
    redirect('/posts/show?id=' . $post['id']);
}
