<?php

function show_categories() {
    require_permission('access_admin_page');
    $categories = all_categories();
    $counts = category_post_counts();
    $errors = flash_get('errors') ?? [];
    $old = flash_get('old') ?? [];
    $success = flash_get('success');
    $error = flash_get('error');
    require __DIR__ . '/../views/admin-categories.php';
}

function post_category_create() {
    require_permission('access_admin_page');
    $name = $_POST['name'] ?? '';
    $slug = $_POST['slug'] ?? '';
    if (trim($slug) === '') {
        $slug = slugify($name);
    }
    $errors = validate_category($name, $slug);

    if (!empty($errors)) {
        flash_set('errors', $errors);
        flash_set('old', ['name' => $name, 'slug' => $slug]);
        redirect('/admin/categories');
        return;
    }

    try {
        create_category($name, $slug);
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            flash_set('error', 'Category already exists.');
            redirect('/admin/categories');
            return;
        }
        flash_set('error', 'Failed to create category.');
        redirect('/admin/categories');
    }

    flash_set('success', 'Category created successfully.');
    redirect('/admin/categories');
}

function post_category_rename() {
    require_permission('access_admin_page');
    $id = (int) ($_POST['id'] ?? 0);
    $name = $_POST['name'] ?? '';
    $slug = $_POST['slug'] ?? '';
    $category = find_category($id);

    if (!$category) {
        flash_set('error', 'Category not found.');
        redirect('/admin/categories');
        return;
    }

    $errors = validate_category($name, $slug);

    if (!empty($errors)) {
        flash_set('errors', $errors);
        flash_set('old', ['name' => $name, 'slug' => $slug]);
        redirect('/admin/categories');
        return;
    }

    try {
        rename_category($id, $name, $slug);
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            flash_set('error', 'Category already exists.');
            redirect('/admin/categories');
            return;
        }
        flash_set('error', 'Failed to rename category.');
        redirect('/admin/categories');
    }

    flash_set('success', 'Category renamed successfully.');
    redirect('/admin/categories');
}

function post_category_delete() {
    require_permission('access_admin_page');
    $id = (int) ($_POST['id'] ?? 0);
    $category = find_category($id);

    if (!$category) {
        flash_set('error', 'Category not found.');
        redirect('/admin/categories');
        return;
    }

    if ($category['is_default']) {
        flash_set('error', 'Default category cannot be deleted.');
        redirect('/admin/categories');
        return;
    }

    $db = db();
    try {
        $db->beginTransaction();
        delete_category($id);
        $db->commit();
    } catch (Throwable $e) {
        $db->rollBack();
        flash_set('error', 'Failed to delete category.');
        redirect('/admin/categories');
    }

    flash_set('success', 'Category deleted successfully.');
    redirect('/admin/categories');
}
