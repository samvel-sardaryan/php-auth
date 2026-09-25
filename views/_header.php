<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'PHP Auth') ?></title>
    <link rel="stylesheet" href="/style.css">
</head>

<body>
    <header class="site-header">
        <nav>
            <?php $viewer = $viewer ?? current_user(); ?>
            <a href="/">Simple Auth</a>
            <ul>
                <li>
                    <a href="/posts">All Posts</a>
                </li>
                <?php if (!is_logged_in()): ?>
                    <li>
                        <a href="/login">Login</a>
                    </li>
                    <li>
                        <a href="/register">Register</a>
                    </li>
                <?php else: ?>
                    <li>
                        <a href="/dashboard">Dashboard</a>
                    </li>
                    <li>
                        <a href="/profile">Profile</a>
                    </li>
                <?php endif; ?>
                <?php if (is_logged_in() && is_verified($viewer)): ?>
                    <li>
                        <a href="/posts/create">New Post</a>
                    </li>
                <?php endif; ?>
                <?php if (can('access_moderator_page')): ?>
                    <li>
                        <a href="/moderator">Moderator</a>
                    </li>
                <?php endif; ?>
                <?php if (can('access_admin_page')): ?>
                    <li>
                        <a href="/admin">Admin</a>
                    </li>
                <?php endif; ?>
                <?php if (is_logged_in()): ?>
                    <li>
                        <form action="/logout" method="post">
                            <?= csrf_field() ?>
                            <button type="submit">Logout</button>
                        </form>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <main class="container">