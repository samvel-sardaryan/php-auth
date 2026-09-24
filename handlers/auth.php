<?php

// Registration, login, logout, email verification and password reset.

function handle_home() {
    require_guest();
    redirect('/login');
}

function show_register() {
    require_guest();
    $name = '';
    $email = '';
    $errors = [];

    require __DIR__ . '/../views/register.php';
}

function post_register() {
    require_guest();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $errors = validate_registration($name, $email, $password);

    if (empty($errors) && email_exists($email)) {
        $errors['email'] = 'Email already registered.';
    }
    if (!empty($errors)) {
        require __DIR__ . '/../views/register.php';
        return;
    }
    $id = create_user($name, $email, $password);
    $token = set_verification_token($id);
    send_verification_email($email, $token);

    flash_set('success', 'Registration successful. Please check your email to verify your account.');
    redirect('/login');
}

function show_login() {
    require_guest();
    $email = '';
    $error = flash_get('error');
    $success = flash_get('success');

    require __DIR__ . '/../views/login.php';
}

function post_login() {
    require_guest();

    $email = trim($_POST['email'] ?? '');
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $password = $_POST['password'] ?? '';
    $error = null;
    $success = null;
    $errors = validate_login($email, $password);

    if (!empty($errors)) {
        require __DIR__ . '/../views/login.php';
        return;
    }

    if (too_many_login_attempts($email, $ip)) {
        log_activity('login.blocked');
        flash_set('error', 'Too many login attempts. Please try again later.');
        redirect('/login');
    }

    $id = attempt_login($email, $password);

    if ($id === null) {
        record_failed_login($email, $ip);
        log_activity('login.failed');
        $error = 'Invalid email or password.';
        require __DIR__ . '/../views/login.php';
        return;
    }

    clear_login_attempts($email);
    login_user($id);
    redirect('/dashboard');
}

function post_logout() {
    logout_user();
    redirect('/login');
}

function verify_email() {
    $token = $_GET['token'] ?? '';
    $verified = verify_email_token($token);

    if ($verified) {
        flash_set('success', is_logged_in()
            ? 'Email verified successfully.'
            : 'Email verified successfully. You can now log in.');
    } else {
        flash_set('error', 'Invalid or expired verification token.');
    }
    redirect(is_logged_in() ? '/dashboard' : '/login');
}

function show_verify_notice() {
    require_login();
    if (is_verified(current_user())) {
        redirect('/dashboard');
        return;
    }

    $success = flash_get('success');
    $error = flash_get('error');
    require __DIR__ . '/../views/verify-notice.php';
}

function post_resend_verification() {
    require_login();
    $user = current_user();
    if (!$user) redirect('/login');
    $token = set_verification_token($user['id']);
    if (send_verification_email($user['email'], $token)) {
        flash_set('success', 'Verification email sent. Please check your email.');
    } else {
        flash_set('error', 'Failed to send verification email. Please try again later.');
    }
    redirect('/verify-notice');
}

function show_forgot() {
    require_guest();
    $error = flash_get('error');
    $success = flash_get('success');
    $email = '';
    require __DIR__ . '/../views/forgot-password.php';
}

function post_forgot() {
    require_guest();
    $email = trim($_POST['email'] ?? '');
    $error = null;
    $success = null;

    if (empty($email)) {
        $error = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } else {
        $token = set_reset_token($email);
        if ($token) {
            send_reset_password_email($email, $token);
        }
        flash_set('success', 'If that email is registered, a reset link has been sent.');
        redirect('/forgot-password');
    }

    require __DIR__ . '/../views/forgot-password.php';
}

function show_reset() {
    require_guest();
    $password = '';
    $confirm = '';

    $token = $_GET['token'] ?? '';
    $user = find_user_by_reset_token($token);
    $error = flash_get('error');
    $success = flash_get('success');
    $errors = flash_get('errors') ?? [];

    if (!$user) {
        redirect('/forgot-password');
        return;
    }

    require __DIR__ . '/../views/reset-password.php';
}

function post_reset() {
    require_guest();
    $token = $_POST['token'] ?? '';
    $user = find_user_by_reset_token($token);

    if (!$user) {
        flash_set('error', 'Invalid or expired reset token.');
        redirect('/reset-password');
        return;
    }

    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    $errors = validate_reset($password, $confirm);

    if (!empty($errors)) {
        flash_set('errors', $errors);
        redirect('/reset-password?token=' . $token);
        return;
    }

    reset_user_password($user, $password);
    log_activity('password.reset', 'user', $user);
    flash_set('success', 'Password reset successfully. You can now log in.');
    redirect('/login');
}
