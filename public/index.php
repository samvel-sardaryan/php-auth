<?php

require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/validation.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/authz.php';
require_once __DIR__ . '/../src/roles.php';

$path = rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/', '/') ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if (PHP_SAPI === 'cli-server') {
    $root = __DIR__;

    $resolved = realpath($root . $path);

    if (is_file($resolved) && str_starts_with($resolved, $root . DIRECTORY_SEPARATOR)) {
        return false;
    }
}

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => false,
    'path' => '/',
]);

session_start();

error_log(sprintf('--> %s %s', $method, $path));

$routes = [
    'GET /' => 'handle_home',
    'GET /register' => 'show_register',
    'POST /register' => 'post_register',
    'GET /login' => 'show_login',
    'POST /login' => 'post_login',
    'GET /dashboard' => 'show_dashboard',
    'POST /logout' => 'post_logout',
    'GET /verify-email' => 'verify_email',
    'GET /verify-notice' => 'show_verify_notice',
    'POST /resend-verification' => 'post_resend_verification',
    'GET /forgot-password' => 'show_forgot',
    'POST /forgot-password' => 'post_forgot',
    'GET /reset-password' => 'show_reset',
    'POST /reset-password' => 'post_reset',
    'GET /moderator' => 'show_moderator',
    'GET /admin' => 'show_admin',
    'GET /admin/users' => 'show_admin_users',
    'POST /admin/users/role' => 'post_admin_users_role'
];

$lookup = $method . ' ' . $path;

if (isset($routes[$lookup])) {
    $routes[$lookup]();
    exit;
}

http_response_code(404);
echo '404 Not Found';
exit;

function handle_home() {
    redirect('/login');
}

function show_register() {
    $name = '';
    $email = '';
    $errors = [];

    require __DIR__ . '/../views/register.php';
}

function post_register() {
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
    $email = '';
    $error = flash_get('error');
    $success = flash_get('success');

    require __DIR__ . '/../views/login.php';
}

function post_login() {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $error = null;
    $success = null;
    $errors = validate_login($email, $password);

    if (!empty($errors)) {
        require __DIR__ . '/../views/login.php';
        return;
    }

    $id = attempt_login($email, $password);

    if ($id === null) {
        $error = 'Invalid email or password.';
        require __DIR__ . '/../views/login.php';
        return;
    }

    login_user($id);
    redirect('/dashboard');
}

function show_dashboard() {
    require_permission('view_dashboard');
    header('Cache-Control: no-store');
    $user = current_user();
    require __DIR__ . '/../views/dashboard.php';
}

function post_logout() {
    logout_user();
    redirect('/login');
}

function verify_email() {
    $token = $_GET['token'] ?? '';
    $verified = verify_email_token($token);

    if ($verified) {
        flash_set('success', 'Email verified successfully. You can now log in.');
    } else {
        flash_set('error', 'Invalid or expired verification token.');
    }
    redirect('/login');
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
    $error = flash_get('error');
    $success = flash_get('success');
    $email = '';
    require __DIR__ . '/../views/forgot-password.php';
}

function post_forgot() {
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
    flash_set('success', 'Password reset successfully. You can now log in.');
    redirect('/login');
}

function show_moderator() {
    require_permission('access_moderator_page');
    require __DIR__ . '/../views/moderator.php';
}

function show_admin() {
    require_permission('access_admin_page');
    require __DIR__ . '/../views/admin.php';
}

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
    $roleId = (int)($_POST['role_id'] ?? 0);
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
