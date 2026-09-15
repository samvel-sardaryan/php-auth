<h1>Profile</h1>

<?php if ($success): ?>
    <p class="success"><?= e($success) ?></p>
<?php endif; ?>
<?php if ($error): ?>
    <p class="error"><?= e($error) ?></p>
<?php endif; ?>

<h2>Profile picture</h2>
<div>
    <?php if ($profile['avatar']): ?>
        <img src="/uploads/avatars/<?= e($profile['avatar']) ?>" alt="Profile picture" width="120" height="120">
        <form method="post" action="/profile/avatar/delete">
            <?= csrf_field() ?>
            <button type="submit">Remove picture</button>
        </form>
    <?php else: ?>
        <span class="avatar-placeholder"><?= e(mb_strtoupper(mb_substr($user['name'], 0, 1))) ?></span>
        <p class="hint">No profile picture yet.</p>
    <?php endif; ?>
</div>
<form method="post" action="/profile/avatar" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div>
        <label for="avatar">Choose an image</label>
        <input type="file" id="avatar" name="avatar" accept="image/*">
        <p class="hint">JPG, PNG, GIF or WEBP, up to 2MB.</p>
    </div>
    <button type="submit">Upload</button>
</form>

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

<h2>Profile details</h2>
<form method="post" action="/profile/details" novalidate>
    <?= csrf_field() ?>
    <div>
        <label for="first_name">First Name</label>
        <input type="text" id="first_name" name="first_name" value="<?= e($old['first_name'] ?? $profile['first_name']) ?>">
        <?php if (isset($errors['first_name'])): ?>
            <span class="error"><?= e($errors['first_name']) ?></span>
        <?php endif; ?>
    </div>
    <div>
        <label for="last_name">Last Name</label>
        <input type="text" id="last_name" name="last_name" value="<?= e($old['last_name'] ?? $profile['last_name']) ?>">
        <?php if (isset($errors['last_name'])): ?>
            <span class="error"><?= e($errors['last_name']) ?></span>
        <?php endif; ?>
    </div>
    <div>
        <label for="phone">Phone</label>
        <input type="text" id="phone" name="phone" value="<?= e($old['phone'] ?? $profile['phone']) ?>">
        <?php if (isset($errors['phone'])): ?>
            <span class="error"><?= e($errors['phone']) ?></span>
        <?php endif; ?>
    </div>
    <div>
        <label for="location">Location</label>
        <input type="text" id="location" name="location" value="<?= e($old['location'] ?? $profile['location']) ?>">
        <?php if (isset($errors['location'])): ?>
            <span class="error"><?= e($errors['location']) ?></span>
        <?php endif; ?>
    </div>
    <div>
        <label for="bio">Bio</label>
        <textarea id="bio" name="bio"><?= e($old['bio'] ?? $profile['bio']) ?></textarea>
        <?php if (isset($errors['bio'])): ?>
            <span class="error"><?= e($errors['bio']) ?></span>
        <?php endif; ?>
    </div>
    <div>
        <label for="date_of_birth">Date of Birth</label>
        <input type="date" id="date_of_birth" name="date_of_birth" value="<?= e($old['date_of_birth'] ?? $profile['date_of_birth']) ?>">
        <?php if (isset($errors['date_of_birth'])): ?>
            <span class="error"><?= e($errors['date_of_birth']) ?></span>
        <?php endif; ?>
    </div>
    <button type="submit">Save</button>
</form>

<h2>My posts</h2>
<p><a href="/posts/create">New post</a></p>
<?php if (empty($posts)): ?>
    <p class="hint">You have not posted yet.</p>
<?php else: ?>
    <?php foreach ($posts as $post): ?>
        <?php require __DIR__ . '/_post.php'; ?>
    <?php endforeach; ?>
<?php endif; ?>

<a href="/dashboard">Back to dashboard</a>