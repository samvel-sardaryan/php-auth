<?php $title = 'Forgot Password'; ?>
<?php require __DIR__ . '/_header.php'; ?>

<form method="post" action="/forgot-password" novalidate>
    <?= csrf_field() ?>
    <label for="email">Email</label>
    <input type="email" id="email" name="email" value="<?= e($email) ?>" required>
    <button type="submit">Send Reset Link</button>
</form>
<form action="/login" method="GET">
    <button type="submit">Back to Login</button>
</form>
<?php if ($success): ?>
    <p class="success"><?= e($success) ?></p>
<?php endif; ?>
<?php if ($error): ?>
    <p class="error"><?= e($error) ?></p>
<?php endif; ?>

<?php require __DIR__ . '/_footer.php'; ?>