<?php

// The feed and post create/edit/delete.

function normalise_files_array($file) {
    if (!is_array($file) || !isset($file['name']) || !is_array($file['name'])) {
        return [];
    }

    $count = count($file['name']);
    $normalised = [];
    for ($i = 0; $i < $count; $i++) {
        $normalised[] = [
            'name' => $file['name'][$i],
            'type' => $file['type'][$i],
            'tmp_name' => $file['tmp_name'][$i],
            'error' => $file['error'][$i],
            'size' => $file['size'][$i],
        ];
    }
    return $normalised;
}

function attach_post_extras(array $posts) {
    if (empty($posts)) {
        return $posts;
    }

    $ids = array_column($posts, 'id');
    $tags = tags_for_posts($ids);
    $images = images_for_posts($ids);

    foreach ($posts as &$post) {
        $post['tags'] = $tags[$post['id']] ?? [];
        $post['images'] = $images[$post['id']] ?? [];
    }
    unset($post);

    return $posts;
}

function show_posts() {
    require_verified();
    $user = current_user();
    $posts = attach_post_extras(list_posts());
    $error = flash_get('error');
    $success = flash_get('success');
    require __DIR__ . '/../views/posts.php';
}

function show_post_create() {
    require_verified();
    $post = null;
    $images = [];
    $errors = flash_get('errors') ?? [];
    $error = flash_get('error');
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
    $user = current_user();
    $errors = validate_post($title, $content, $status, $categoryId);
    $tags = $_POST['tags'] ?? '';
    $old = [
        'title' => $title,
        'content' => $content,
        'status' => $status,
        'category_id' => $categoryId,
        'tags' => $tags
    ];

    $files = array_values(array_filter(
        normalise_files_array($_FILES['images'] ?? []),
        fn($f) => ($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE
    ));

    if (count($files) > POST_IMAGE_MAX_COUNT) {
        $errors['images'] = 'You can attach at most ' . POST_IMAGE_MAX_COUNT . ' images.';
    }

    if (!empty($errors)) {
        flash_set('errors', $errors);
        flash_set('old', $old);
        redirect('/posts/create');
    }

    $stored = [];
    foreach ($files as $file) {
        $imageError = null;
        $filename = store_post_image($file, $imageError);

        if (!$filename) {
            foreach ($stored as $done) {
                delete_post_image_file($done);
            }
            flash_set('error', $imageError);
            flash_set('old', $old);
            redirect('/posts/create');
        }

        $stored[] = $filename;
    }

    $tagNames = normalise_tags($tags);
    $db = db();
    try {
        $db->beginTransaction();
        $postId = create_post($user['id'], $title, $content, $status, $categoryId);
        $tagIds = find_or_create_tags($tagNames);
        set_post_tags($postId, $tagIds);
        add_post_images($postId, $stored);
        $db->commit();
    } catch (Throwable $e) {
        $db->rollBack();
        foreach ($stored as $done) {
            delete_post_image_file($done);
        }
        error_log('Failed to create post: ' . $e->getMessage());
        flash_set('error', 'Failed to create post.');
        flash_set('old', $old);
        redirect('/posts/create');
    }

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

    $images = images_for_post($post['id']);

    require_post_owner($post);

    $errors = flash_get('errors') ?? [];
    $error = flash_get('error');
    $old = flash_get('old') ?? [
        'title' => $post['title'],
        'content' => $post['content'],
        'status' => $post['status'],
        'category_id' => $post['category_id'],
        'tags' => implode(', ', tags_for_post($post['id'])),
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
    $tagNames = normalise_tags($_POST['tags'] ?? '');
    $errors = validate_post($title, $content, $status, $categoryId);
    $existing = images_for_post($post['id']);
    $existingIds = array_column($existing, 'id');
    $removing = array_map(fn($id) => (int)$id, (array)($_POST['remove_images'] ?? []));
    $intersection = array_intersect($existingIds, $removing);
    $files = array_values(array_filter(
        normalise_files_array($_FILES['images'] ?? []),
        fn($f) => ($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE
    ));
    $old = [
        'title' => $title,
        'content' => $content,
        'status' => $status,
        'category_id' => $categoryId,
        'tags' => $_POST['tags'] ?? ''
    ];

    if (count($existing) - count($intersection) + count($files) > POST_IMAGE_MAX_COUNT) {
        $errors['images'] = 'You can attach at most ' . POST_IMAGE_MAX_COUNT . ' images.';
    }

    $removedPaths = [];
    foreach ($existing as $img) {
        if (in_array($img['id'], $intersection)) {
            $removedPaths[] = $img['path'];
        }
    }

    if (!empty($errors)) {
        flash_set('errors', $errors);
        flash_set('old', $old);
        redirect('/posts/edit?id=' . $post['id']);
        return;
    }

    $stored = [];
    foreach ($files as $file) {
        $imageError = null;
        $filename = store_post_image($file, $imageError);

        if (!$filename) {
            foreach ($stored as $done) {
                delete_post_image_file($done);
            }
            flash_set('error', $imageError);
            flash_set('old', $old);
            redirect('/posts/edit?id=' . $post['id']);
        }

        $stored[] = $filename;
    }

    $db = db();
    try {
        $db->beginTransaction();
        update_post($post['id'], $title, $content, $status, $categoryId);
        $tagIds = find_or_create_tags($tagNames);
        set_post_tags($post['id'], $tagIds);
        add_post_images($post['id'], $stored);
        delete_post_images($post['id'], $intersection);
        $db->commit();
    } catch (Throwable $e) {
        $db->rollBack();
        foreach ($stored as $file) {
            delete_post_image_file($file);
        }
        error_log('Failed to update post: ' . $e->getMessage());
        flash_set('error', 'Failed to update post.');
        flash_set('errors', $errors);
        flash_set('old', $old);
        redirect('/posts/edit?id=' . $post['id']);
        return;
    }
    foreach ($removedPaths as $path) {
        delete_post_image_file($path);
    }
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

function show_post() {
    $user = current_user();
    $post = find_post((int) ($_GET['id'] ?? 0), $user['id'] ?? null, can('manage_posts'));

    if (!$post) {
        http_response_code(404);
        echo '404 Not Found';
        exit;
    }

    $comments = comments_for_post($post['id']);
    $post = attach_post_extras([$post])[0];
    $replyTo = (int) ($_GET['reply_to'] ?? 0) ?: null;

    $success = flash_get('success');
    $errors = flash_get('errors') ?? [];
    $error = flash_get('error');
    $old = flash_get('old');

    require __DIR__ . '/../views/post.php';
}
