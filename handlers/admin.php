<?php

// Admin pages: users, deleted posts and the activity log.

function show_admin_users() {
    require_permission('view_users');
    $success = flash_get('success');
    $error = flash_get('error');
    $users = list_users();
    $roles = all_roles();
    require __DIR__ . '/../views/admin-users.php';
}

function post_admin_users_role() {
    require_permission('manage_users');
    $userId = (int) ($_POST['user_id'] ?? 0);
    $roleId = (int) ($_POST['role_id'] ?? 0);
    if (!role_exists($roleId)) {
        flash_set('error', 'Invalid role.');
        redirect('/admin/users');
    } elseif (!user_exists($userId)) {
        flash_set('error', 'Invalid user.');
        redirect('/admin/users');
    } elseif ($userId === current_user()['id']) {
        flash_set('error', 'You cannot change your own role.');
        redirect('/admin/users');
    }
    assign_role($userId, $roleId);
    log_activity('role.changed', 'user', $userId);
    flash_set('success', 'Role assigned successfully.');
    redirect('/admin/users');
}

function show_deleted_posts() {
    require_permission('view_deleted_posts');
    $success = flash_get('success');
    $error = flash_get('error');
    $posts = list_deleted_posts();
    require __DIR__ . '/../views/deleted-posts.php';
}

function post_restore_post() {
    require_permission('view_deleted_posts');
    $post = find_deleted_post((int) ($_POST['post_id'] ?? 0));
    if (!$post) {
        flash_set('error', 'Post not found.');
    } elseif ($post['deleted_at'] === null) {
        flash_set('error', 'Post is not deleted.');
    } else {
        restore_post($post['id']);
        flash_set('success', 'Post restored successfully.');
    }
    redirect('/admin/deleted-posts');
}

function show_activity() {
    require_permission('access_admin_page');
    $success = flash_get('success');
    $error = flash_get('error');
    $filterUserId = isset($_GET['user_id']) ? (int)($_GET['user_id']) : null;
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = 20;
    $total = count_activity($filterUserId);
    $pages = max(1, (int)ceil($total / $limit));
    if ($page > $pages) {
        $page = $pages;
    }
    $offset = ($page - 1) * $limit;
    $activity = list_activity($filterUserId, $limit, $offset);
    $users = list_users();
    require __DIR__ . '/../views/activity-log.php';
}
