<?php

require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/validation.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/authz.php';
require_once __DIR__ . '/../src/users.php';
require_once __DIR__ . '/../src/roles.php';
require_once __DIR__ . '/../src/profiles.php';
require_once __DIR__ . '/../src/upload.php';
require_once __DIR__ . '/../src/posts.php';
require_once __DIR__ . '/../src/categories.php';
require_once __DIR__ . '/../src/tags.php';
require_once __DIR__ . '/../src/comments.php';

require_once __DIR__ . '/../handlers/auth.php';
require_once __DIR__ . '/../handlers/pages.php';
require_once __DIR__ . '/../handlers/admin.php';
require_once __DIR__ . '/../handlers/profile.php';
require_once __DIR__ . '/../handlers/categories.php';
require_once __DIR__ . '/../handlers/posts.php';
require_once __DIR__ . '/../handlers/comments.php';

$path = rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/', '/') ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if (PHP_SAPI === 'cli-server') {
    $root = __DIR__;

    $resolved = realpath($root . $path);

    if (is_file($resolved) && str_starts_with($resolved, $root . DIRECTORY_SEPARATOR) && $resolved !== __FILE__) {
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
    'POST /admin/users/role' => 'post_admin_users_role',
    'GET /profile' => 'show_profile',
    'POST /profile' => 'post_profile',
    'POST /profile/password' => 'post_profile_password',
    'POST /profile/details' => 'post_profile_details',
    'POST /profile/avatar/delete' => 'post_profile_avatar_delete',
    'GET /users' => 'show_user_profile',
    'GET /posts' => 'show_posts',
    'GET /posts/create' => 'show_post_create',
    'POST /posts/create' => 'post_post_create',
    'GET /posts/edit' => 'show_post_edit',
    'POST /posts/edit' => 'post_post_edit',
    'POST /posts/delete' => 'post_post_delete',
    'GET /admin/categories' => 'show_categories',
    'POST /admin/categories/create' => 'post_category_create',
    'POST /admin/categories/rename' => 'post_category_rename',
    'POST /admin/categories/delete' => 'post_category_delete',
    'GET /posts/show' => 'show_post',
    'POST /comments/create' => 'post_comment_create',
    'GET /comments/edit' => 'show_comment_edit',
    'POST /comments/edit' => 'post_comment_edit',
    'POST /comments/delete' => 'post_comment_delete',
];

$lookup = $method . ' ' . $path;

if (
    $method === 'POST' && empty($_POST) && empty($_FILES)
    && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0
) {
    http_response_code(413);
    echo '413 Content Too Large';
    exit;
}

if ($method === 'POST') {
    if (!csrf_verify($_POST['_csrf'] ?? null)) {
        deny();
    }
}

if (isset($routes[$lookup])) {
    $routes[$lookup]();
    exit;
}

http_response_code(404);
echo '404 Not Found';
exit;
