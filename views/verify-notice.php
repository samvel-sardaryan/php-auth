<form method="post" action="/resend-verification" novalidate>
    <p>Please verify your email to continue.</p>
    <button type="submit">Resend Notification</button>
    <?php if($success): ?>
        <p class="success"><?= e($success) ?></p>
    <?php endif; ?>
    <?php if($error): ?>
        <p class="error"><?= e($error) ?></p>
    <?php endif; ?>
</form>
<form action="/logout" method="POST">
    <button type="submit">Logout</button>
</form>