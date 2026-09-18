<article class="post">
    <h3><?= e($post['title']) ?></h3>
    <p class="meta">
        by <a href="/users?id=<?= e($post['user_id']) ?>"><?= e($post['author_name']) ?></a>
        in <?= e($post['category_name']) ?>
        on <?= e($post['created_at']) ?>
        <?php if ($post['status'] !== 'published'): ?>
            <span class="status-badge"><?= e(strtoupper($post['status'])) ?></span>
        <?php endif; ?>
    </p>
    <div class="content"><?= nl2br(e($post['content'])) ?></div>
    <?php if (!empty($post['images'])): ?>
        <p class="images">
            <?php foreach ($post['images'] as $image): ?>
                <img src="/uploads/posts/<?= e($image) ?>" alt="" width="160">
            <?php endforeach; ?>
        </p>
    <?php endif; ?>
    <?php if (!empty($post['tags'])): ?>
        <p class="tags">
            <?php foreach ($post['tags'] as $tag): ?>
                <a href="/posts?tag=<?= urlencode($tag) ?>">#<?= e($tag) ?></a>
            <?php endforeach; ?>
        </p>
    <?php endif; ?>
    <?php if (owns_post($post) || can('manage_posts')): ?>
        <p class="actions">
            <a href="/posts/edit?id=<?= e($post['id']) ?>">Edit</a>
        <form method="post" action="/posts/delete" onsubmit="return confirm('Delete this post?')">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= e($post['id']) ?>">
            <button type="submit">Delete</button>
        </form>
        </p>
    <?php endif; ?>
</article>