<?php $title = $user['name']; ?>
<?php require __DIR__ . '/_header.php'; ?>

<h1><?= e($user['name']) ?></h1>
<p><span>Role:</span> <?= e($user['role_name']) ?></p>
<p><span>Joined:</span> <?= e($user['created_at']) ?></p>

<div>
    <?php if ($profile['avatar']): ?>
        <img src="/uploads/avatars/<?= e($profile['avatar']) ?>" alt="Profile picture" width="120" height="120">
    <?php else: ?>
        <span class="avatar-placeholder"><?= e(mb_strtoupper(mb_substr($user['name'], 0, 1))) ?></span>
    <?php endif; ?>
</div>

<h2>Profile details</h2>
<?php
$details = [
    'First Name'    => $profile['first_name'],
    'Last Name'     => $profile['last_name'],
    'Phone'         => $profile['phone'],
    'Location'      => $profile['location'],
    'Date of Birth' => $profile['date_of_birth'],
    'Bio'           => $profile['bio'],
];
$filled = array_filter($details, fn($v) => $v !== null && $v !== '');
?>
<?php if (empty($filled)): ?>
    <p class="hint">This user has not filled in their profile yet.</p>
<?php else: ?>
    <?php foreach ($filled as $label => $value): ?>
        <p><span><?= e($label) ?>:</span> <?= nl2br(e($value)) ?></p>
    <?php endforeach; ?>
<?php endif; ?>

<h2>Posts</h2>
<?php if (empty($posts)): ?>
    <p class="hint">This user has not posted yet.</p>
<?php else: ?>
    <?php foreach ($posts as $post): ?>
        <?php require __DIR__ . '/_post.php'; ?>
    <?php endforeach; ?>
<?php endif; ?>

<a href="/dashboard">Back to dashboard</a>

<?php require __DIR__ . '/_footer.php'; ?>