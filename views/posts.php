<h1>Posts</h1>

<?php if ($success): ?>
    <p class="success"><?= e($success) ?></p>
<?php endif; ?>
<?php if ($error): ?>
    <p class="error"><?= e($error) ?></p>
<?php endif; ?>

<p><a href="/posts/create">New post</a></p>

<?php if (empty($posts)): ?>
    <p class="hint">No posts yet. Be the first to write one.</p>
<?php else: ?>
    <?php foreach ($posts as $post): ?>
        <?php require __DIR__ . '/_post.php'; ?>
    <?php endforeach; ?>
<?php endif; ?>

<a href="/dashboard">Back to dashboard</a>
