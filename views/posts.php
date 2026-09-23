<?php $viewer = current_user(); ?>

<h1>Posts</h1>

<?php if ($success): ?>
    <p class="success"><?= e($success) ?></p>
<?php endif; ?>
<?php if ($error): ?>
    <p class="error"><?= e($error) ?></p>
<?php endif; ?>

<?php if ($viewer && is_verified($viewer)): ?>
    <p><a href="/posts/create">New post</a></p>
<?php endif; ?>

<!-- GET, so a filtered feed is a shareable URL and needs no CSRF token -->
<form method="get" action="/posts" class="filters">
    <input type="search" name="q" value="<?= e($p['q']) ?>" placeholder="Search posts">
    <select name="category">
        <option value="">All categories</option>
        <?php foreach ($categories as $c): ?>
            <option value="<?= e($c['id']) ?>" <?= (int) $p['category'] === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <input type="text" name="tag" value="<?= e($p['tag']) ?>" placeholder="Tag">
    <select name="sort">
        <?php foreach (['newest' => 'Newest', 'liked' => 'Most liked', 'commented' => 'Most discussed'] as $key => $label): ?>
            <option value="<?= e($key) ?>" <?= $p['sort'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
    </select>
    <?php if (!empty($p['author'])): ?>
        <input type="hidden" name="author" value="<?= e($p['author']) ?>">
    <?php endif; ?>
    <button type="submit">Search</button>
    <a href="/posts">Clear</a>
</form>

<?php if (empty($posts)): ?>
    <p class="hint">
        <?= $total === 0 && $p['q'] === '' && $p['tag'] === '' && !$p['category'] && !$p['author']
            ? 'No posts yet. Be the first to write one.'
            : 'No posts match those filters.' ?>
    </p>
<?php else: ?>
    <?php foreach ($posts as $post): ?>
        <?php require __DIR__ . '/_post.php'; ?>
    <?php endforeach; ?>

    <nav class="pagination">
        <?php if ($p['page'] > 1): ?>
            <a href="<?= e(feed_url($p, $p['page'] - 1)) ?>">Previous</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $pages; $i++): ?>
            <?php if ($i === $p['page']): ?>
                <strong><?= e($i) ?></strong>
            <?php else: ?>
                <a href="<?= e(feed_url($p, $i)) ?>"><?= e($i) ?></a>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($p['page'] < $pages): ?>
            <a href="<?= e(feed_url($p, $p['page'] + 1)) ?>">Next</a>
        <?php endif; ?>
    </nav>

    <p class="count"><?= e($total) ?> <?= $total === 1 ? 'post' : 'posts' ?>, page <?= e($p['page']) ?> of <?= e($pages) ?></p>
<?php endif; ?>

<a href="/dashboard">Back to dashboard</a>
