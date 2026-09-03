<form method="post" action="/register" novalidate>
    <div>
        <label for="name">Name</label>
        <input type="text" id="name" name="name" value="<?= e($name) ?>" required>
        <?php if(isset($errors['name'])) : ?>
            <span class="error"><?= e($errors['name']) ?></span>
        <?php endif; ?>
    </div>
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
    <button type="submit">Register</button>
</form>