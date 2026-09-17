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