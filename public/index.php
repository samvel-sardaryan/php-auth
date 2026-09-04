<?php

require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/validation.php';
require_once __DIR__ . '/../src/auth.php';

$path = rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/', '/') ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if(PHP_SAPI === 'cli-server'){
    $root = __DIR__;

    $resolved = realpath($root . $path);

    if(is_file($resolved) && str_starts_with($resolved, $root . DIRECTORY_SEPARATOR)) {
        return false;
    }
}

session_set_cookie_params([
    'httponly'=>true,
    'samesite'=>'Lax',
    'secure'=>false,
    'path'=> '/',
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
];

$lookup = $method . ' ' . $path;

if(isset($routes[$lookup])){
    $routes[$lookup]();
    exit;
}

http_response_code(404);
echo '404 Not Found';
exit;

function handle_home()
{
    redirect('/login');
}

function show_register()
{
    $name = '';
    $email = '';
    $errors = [];
    
    require __DIR__ . '/../views/register.php';
}

function post_register()
{
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $errors = validate_registration($name, $email, $password);

    if(empty($errors) && email_exists($email)) {
        $errors['email'] = 'Email already registered.';
    }
    if(!empty($errors)) {
        require __DIR__ . '/../views/register.php';
        return;
    }
    create_user($name, $email, $password);
    redirect('/login');
}

function show_login()
{
    $email = '';
    $error = null;
    require __DIR__ . '/../views/login.php';
}

function post_login()
{
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $error = null;
    $errors = validate_login($email, $password);

    if(!empty($errors)) {
        require __DIR__ . '/../views/login.php';
        return;
    }

    $id = attempt_login($email, $password);

    if($id === null) {
        $error = 'Invalid email or password.';
        require __DIR__ . '/../views/login.php';
        return;
    }
    
    login_user($id);
    redirect('/dashboard');
}

function show_dashboard()
{
    require_login();
    $user = current_user();
    require __DIR__ . '/../views/dashboard.php';
}

function post_logout()
{
    logout_user();
    redirect('/login');
}