<form method="post" action="/login" novalidate>
    <div>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= e($email) ?>" required>
        <?php if(isset($errors['email'])) : ?>
            <span class="error"><?= e($errors['email']) ?></span>
        <?php endif; ?>
    </div>
    <div>
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
        <?php if(isset($errors['password'])) : ?>
            <span class="error"><?= e($errors['password']) ?></span>
        <?php endif; ?>
    </div>
    <?php if($error): ?>
        <p class="error"><?= e($error) ?></p>
    <?php endif; ?>
    <button type="submit">Login</button>
</form>