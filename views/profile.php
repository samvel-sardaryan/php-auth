<h1>Profile</h1>

<?php if ($success): ?>
    <p class="success"><?= e($success) ?></p>
<?php endif; ?>
<?php if ($error): ?>
    <p class="error"><?= e($error) ?></p>
<?php endif; ?>

<h2>Account details</h2>
<form method="post" action="/profile" novalidate>
    <?= csrf_field() ?>
    <div>
        <label for="name">Name</label>
        <input type="text" id="name" name="name" value="<?= e($old['name'] ?? $user['name']) ?>">
        <?php if (isset($errors['name'])): ?>
            <span class="error"><?= e($errors['name']) ?></span>
        <?php endif; ?>
    </div>
    <div>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= e($old['email'] ?? $user['email']) ?>">
        <?php if (isset($errors['email'])): ?>
            <span class="error"><?= e($errors['email']) ?></span>
        <?php endif; ?>
        <p class="hint">Changing your email requires verifying the new address.</p>
    </div>
    <button type="submit">Save</button>
</form>

<h2>Change password</h2>
<form method="post" action="/profile/password" novalidate>
    <?= csrf_field() ?>
    <div>
        <label for="current">Current Password</label>
        <input type="password" id="current" name="current">
        <?php if (isset($errors['current'])): ?>
            <span class="error"><?= e($errors['current']) ?></span>
        <?php endif; ?>
    </div>
    <div>
        <label for="new">New Password</label>
        <input type="password" id="new" name="new">
        <?php if (isset($errors['password'])): ?>
            <span class="error"><?= e($errors['password']) ?></span>
        <?php endif; ?>
    </div>
    <div>
        <label for="confirm">Confirm New Password</label>
        <input type="password" id="confirm" name="confirm">
        <?php if (isset($errors['confirm'])): ?>
            <span class="error"><?= e($errors['confirm']) ?></span>
        <?php endif; ?>
    </div>
    <button type="submit">Save</button>
</form>

<a href="/dashboard">Back to dashboard</a>
