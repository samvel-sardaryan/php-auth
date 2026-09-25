<?php $title = 'Dashboard'; ?>
<?php require __DIR__ . '/_header.php'; ?>

<h1>Dashboard</h1>

<?php if ($success): ?>
    <p class="success"><?= e($success) ?></p>
<?php endif; ?>
<?php if ($error): ?>
    <p class="error"><?= e($error) ?></p>
<?php endif; ?>
<div>
    <p><span>Hello</span> <?= e($user['name']) ?></p>
    <p><span>Role:</span> <?= e($user['role_name']) ?></p>
    <p><span>Email:</span> <?= e($user['email']) ?></p>
    <p><span>Created At:</span> <?= e($user['created_at']) ?></p>
    <?php if (can('access_admin_page')): ?>
        <a href="/admin">Admin</a>
    <?php endif; ?>
    <?php if (can('access_moderator_page')): ?>
        <a href="/moderator">Moderator</a>
    <?php endif; ?>
    <?php if (can('view_users')): ?>
        <a href="/admin/users">Admin Users</a>
    <?php endif; ?>
    <a href="/profile">Profile</a>
    <a href="/posts">Posts</a>
    <a href="/posts/create">New Post</a>
    <form action="/logout" method="POST">
        <?= csrf_field() ?>
        <button type="submit">Logout</button>
    </form>
</div>

<?php require __DIR__ . '/_footer.php'; ?>