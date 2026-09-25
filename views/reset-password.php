<?php $title = 'Reset Password'; ?>
<?php require __DIR__ . '/_header.php'; ?>

<form method="post" action="/reset-password" novalidate>
    <?= csrf_field() ?>
    <input type="hidden" name="token" value="<?= e($token) ?>">
    <div class="field">
        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>
    </div>
    <?php if (isset($errors['password'])) : ?>
        <span class="error"><?= e($errors['password']) ?></span>
    <?php endif; ?>
    <div class="field">
        <label for="confirm">Confirm Password</label>
        <input type="password" name="confirm" id="confirm" required>
    </div>
    <?php if (isset($errors['confirm'])) : ?>
        <span class="error"><?= e($errors['confirm']) ?></span>
    <?php endif; ?>
    <?php if ($success): ?>
        <p class="success"><?= e($success) ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p class="error"><?= e($error) ?></p>
    <?php endif; ?>
    <button type="submit">Reset Password</button>
</form>

<?php require __DIR__ . '/_footer.php'; ?>