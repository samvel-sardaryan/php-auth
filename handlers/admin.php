<?php

// Admin user management.

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
    flash_set('success', 'Role assigned successfully.');
    redirect('/admin/users');
}
