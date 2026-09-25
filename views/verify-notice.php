<?php $title = 'Verify your email'; ?>
<?php require __DIR__ . '/_header.php'; ?>

<form method="post" action="/resend-verification" novalidate>
    <?= csrf_field() ?>
    <p>Please verify your email to continue.</p>
    <button type="submit">Resend Notification</button>
    <?php if ($success): ?>
        <p class="success"><?= e($success) ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p class="error"><?= e($error) ?></p>
    <?php endif; ?>
</form>
<form action="/logout" method="POST">
    <?= csrf_field() ?>
    <button type="submit">Logout</button>
</form>

<?php require __DIR__ . '/_footer.php'; ?>