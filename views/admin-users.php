<?php $title = 'Users'; ?>
<?php require __DIR__ . '/_header.php'; ?>

<table border="1">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Email Verified</th>
            <th>Created At</th>
            <th>Profile</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= e($user['id']) ?></td>
                <td><?= e($user['name']) ?></td>
                <td><?= e($user['email']) ?></td>
                <td>
                    <?= e($user['role_name']) ?>
                    <?php if (can('manage_users')): ?>
                        <form method="post" action="/admin/users/role" novalidate>
                            <?= csrf_field() ?>
                            <input type="hidden" name="user_id" value="<?= e($user['id']) ?>">
                            <select name="role_id">
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= e($role['id']) ?>" <?= $role['id'] == $user['role_id'] ? 'selected' : '' ?>><?= e($role['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit">Update</button>
                        </form>
                    <?php endif; ?>
                </td>
                <td><?= e($user['email_verified_at'] ? 'Yes' : 'No') ?></td>
                <td><?= e($user['created_at']) ?></td>
                <td><a href="/users?id=<?= e($user['id']) ?>">View</a></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php if ($success): ?>
    <p class="success"><?= e($success) ?></p>
<?php endif; ?>
<?php if ($error): ?>
    <p class="error"><?= e($error) ?></p>
<?php endif; ?>

<?php require __DIR__ . '/_footer.php'; ?>