<?php

// Pages that only check a permission and render a view.

function show_dashboard() {
    require_permission('view_dashboard');
    $user = current_user();
    $success = flash_get('success');
    $error = flash_get('error');
    require __DIR__ . '/../views/dashboard.php';
}

function show_admin() {
    require_permission('access_admin_page');
    require __DIR__ . '/../views/admin.php';
}

function show_moderator() {
    require_permission('access_moderator_page');
    require __DIR__ . '/../views/moderator.php';
}
