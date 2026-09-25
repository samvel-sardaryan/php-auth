<?php $title = 'Login'; ?>
<?php require __DIR__ . '/_header.php'; ?>

<form method="post" action="/login" novalidate>
    <?= csrf_field() ?>
    <div>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= e($email) ?>" required>
        <?php if (isset($errors['email'])) : ?>
            <span class="error"><?= e($errors['email']) ?></span>
        <?php endif; ?>
    </div>
    <div>
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
        <?php if (isset($errors['password'])) : ?>
            <span class="error"><?= e($errors['password']) ?></span>
        <?php endif; ?>
    </div>
    <?php if ($success): ?>
        <p class="success"><?= e($success) ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p class="error"><?= e($error) ?></p>
    <?php endif; ?>
    <button type="submit">Login</button>
</form>
<form action="/forgot-password" method="GET">
    <button type="submit">Forgot Password</button>
</form>

<?php require __DIR__ . '/_footer.php'; ?>