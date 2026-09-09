<h1>Dashboard</h1>
<div>
    <p><span class="label">Hello</span> <?= e($user['name']) ?></p>
    <p><span class="label">Role:</span> <?= e($user['role_name']) ?></p>
    <p><span class="label">Email:</span> <?= e($user['email']) ?></p>
    <p><span class="label">Created At:</span> <?= e($user['created_at']) ?></p>
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
    <form action="/logout" method="POST">
        <?= csrf_field() ?>
        <button type="submit">Logout</button>
    </form>
</div>