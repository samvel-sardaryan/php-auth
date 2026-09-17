<?php

// The feed and post create/edit/delete.

function show_posts() {
    require_verified();
    $user = current_user();
    $posts = list_posts();
    $error = flash_get('error');
    $success = flash_get('success');
    require __DIR__ . '/../views/posts.php';
}

function show_post_create() {
    require_verified();
    $post = null;
    $errors = flash_get('errors') ?? [];
    $old = flash_get('old') ?? [
        'title' => '',
        'content' => '',
    ];
    $categories = all_categories();
    require __DIR__ . '/../views/post-form.php';
}

function post_post_create() {
    require_verified();
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $status = $_POST['status'] ?? '';
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $errors = validate_post($title, $content, $status, $categoryId);

    if (!empty($errors)) {
        flash_set('errors', $errors);
        flash_set('old', ['title' => $title, 'content' => $content, 'status' => $status, 'category_id' => $categoryId]);
        redirect('/posts/create');
        return;
    }

    $user = current_user();
    create_post($user['id'], $title, $content, $status, $categoryId);
    flash_set('success', 'Post created successfully.');
    redirect('/posts');
}

function show_post_edit() {
    require_verified();
    $user = current_user();
    $post = find_post((int) ($_GET['id'] ?? 0), $user['id'] ?? null, can('manage_posts'));
    $categories = all_categories();

    if (!$post) {
        http_response_code(404);
        echo '404 Not Found';
        exit;
    }

    require_post_owner($post);

    $errors = flash_get('errors') ?? [];
    $old = flash_get('old') ?? [
        'title' => $post['title'],
        'content' => $post['content'],
        'status' => $post['status'],
        'category_id' => $post['category_id'],
    ];
    require __DIR__ . '/../views/post-form.php';
}

function post_post_edit() {
    require_verified();
    $user = current_user();
    $post = find_post((int) ($_POST['id'] ?? 0), $user['id'] ?? null, can('manage_posts'));

    if (!$post) {
        http_response_code(404);
        echo '404 Not Found';
        exit;
    }

    require_post_owner($post);

    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $status = $_POST['status'] ?? '';
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $errors = validate_post($title, $content, $status, $categoryId);

    if (!empty($errors)) {
        flash_set('errors', $errors);
        flash_set('old', ['title' => $title, 'content' => $content, 'status' => $status, 'category_id' => $categoryId]);
        redirect('/posts/edit?id=' . $post['id']);
        return;
    }

    update_post($post['id'], $title, $content, $status, $categoryId);
    flash_set('success', 'Post updated successfully.');
    redirect('/posts');
}

function post_post_delete() {
    require_verified();
    $user = current_user();
    $post = find_post((int) ($_POST['id'] ?? 0), $user['id'] ?? null, can('manage_posts'));

    if (!$post) {
        http_response_code(404);
        echo '404 Not Found';
        exit;
    }

    require_post_owner($post);

    delete_post($post['id']);
    flash_set('success', 'Post deleted successfully.');
    redirect('/posts');
}
