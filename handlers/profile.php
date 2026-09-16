<?php

// Your own profile, and viewing someone else's.

function show_profile() {
    require_login();
    $user = current_user();
    $profile = get_profile($user['id']);
    $posts = list_user_posts($user['id']);
    $errors = flash_get('errors') ?? [];
    $old = flash_get('old') ?? [];
    $success = flash_get('success');
    $error = flash_get('error');
    require __DIR__ . '/../views/profile.php';
}

function post_profile() {
    require_login();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $user = current_user();
    $errors = validate_profile($name, $email);

    if (email_exists_for_other($email, $user['id'])) {
        $errors['email'] = 'Email already exists.';
    }

    if (!empty($errors)) {
        flash_set('errors', $errors);
        flash_set('old', ['name' => $name, 'email' => $email]);
        redirect('/profile');
        return;
    }

    $emailChanged = $email !== $user['email'];

    if ($emailChanged) {
        if (!update_profile($user['id'], $name, $email)) {
            flash_set('error', 'Failed to update profile.');
        } else {
            $db = db();
            $stmt = $db->prepare("UPDATE users SET email_verified_at = NULL, verification_token = NULL, verification_expires_at = NULL WHERE id = ?;");
            $stmt->execute([$user['id']]);
            $token = set_verification_token($user['id']);
            send_verification_email($email, $token);
            flash_set('success', 'Profile updated. Please check your new email address to verify it.');
        }
    } elseif ($name !== $user['name']) {
        if (!update_profile($user['id'], $name, $email)) {
            flash_set('error', 'Failed to update profile.');
        } else {
            flash_set('success', 'Profile updated successfully.');
        }
    }
    redirect('/profile');
}

function post_profile_password() {
    require_login();
    $current = $_POST['current'] ?? '';
    $new = $_POST['new'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    $user = current_user();
    $errors = validate_password_change($current, $new, $confirm);

    $db = db();
    $stmt = $db->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->execute([$user['id']]);
    $hash = $stmt->fetchColumn();

    if (!empty($errors)) {
        flash_set('errors', $errors);
        redirect('/profile');
        return;
    }

    if (!password_verify($current, $hash)) {
        flash_set('error', 'Incorrect current password.');
        redirect('/profile');
        return;
    }

    reset_user_password($user['id'], $new);
    flash_set('success', 'Password changed successfully.');
    redirect('/profile');
}

function post_profile_details() {
    require_login();
    $user = current_user();

    $d = [
        'first_name' => trim($_POST['first_name'] ?? ''),
        'last_name'  => trim($_POST['last_name'] ?? ''),
        'phone'      => trim($_POST['phone'] ?? ''),
        'location'   => trim($_POST['location'] ?? ''),
        'bio'        => trim($_POST['bio'] ?? ''),
        'date_of_birth' => trim($_POST['date_of_birth'] ?? ''),
    ];

    $errors = validate_profile_details($d);

    if (!empty($errors)) {
        flash_set('errors', $errors);
        flash_set('old', $d);
        redirect('/profile');
    }

    if ($d['date_of_birth'] === '') {
        $d['date_of_birth'] = null;
    }

    $filename = null;
    if (($_FILES['avatar']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $error = null;
        $filename = store_avatar($_FILES['avatar'], $error);
        if (!$filename) {
            flash_set('error', $error);
            flash_set('old', $d);
            redirect('/profile');
        }
    }

    $oldAvatar = get_profile($user['id'])['avatar'];

    $db = db();

    try {
        $db->beginTransaction();
        save_profile($user['id'], $d);

        if ($filename !== null) {
            set_avatar($user['id'], $filename);
        }

        $db->commit();
    } catch (Throwable $e) {
        $db->rollBack();
        delete_avatar_file($filename);
        error_log('profile save failed: ' . $e->getMessage());
        flash_set('error', 'Failed to update profile.');
        flash_set('old', $d);
        redirect('/profile');
    }

    if ($filename !== null) {
        delete_avatar_file($oldAvatar);
    }

    flash_set('success', $filename !== null
        ? 'Profile and picture updated successfully.'
        : 'Profile updated successfully.');
    redirect('/profile');
}

function post_profile_avatar_delete() {
    require_login();
    $user = current_user();
    $profile = get_profile($user['id']);
    set_avatar($user['id'], null);
    delete_avatar_file($profile['avatar']);
    flash_set('success', 'Avatar deleted successfully.');
    redirect('/profile');
}

function show_user_profile() {
    require_verified();
    $user = find_user((int) ($_GET['id'] ?? 0));
    if (!$user) {
        http_response_code(404);
        echo '404 Not Found';
        exit;
    }

    if (current_user()['id'] === $user['id']) {
        redirect('/profile');
    }

    $profile = get_profile($user['id']);
    $posts = list_user_posts($user['id']);

    require __DIR__ . '/../views/user-profile.php';
}
