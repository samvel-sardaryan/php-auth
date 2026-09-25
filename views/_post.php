<?php $viewer = $viewer ?? current_user(); ?>
<article class="post">
    <h3><a href="/posts/show?id=<?= e($post['id']) ?>"><?= e($post['title']) ?></a></h3>
    <p class="meta">
        by <a href="/users?id=<?= e($post['user_id']) ?>"><?= e($post['author_name']) ?></a>
        in <?= e($post['category_name']) ?>
        on <?= e($post['created_at']) ?>
        <?php if ($post['status'] !== 'published'): ?>
            <span class="status-badge"><?= e(strtoupper($post['status'])) ?></span>
        <?php endif; ?>
    </p>
    <div><?= nl2br(e($post['content'])) ?></div>
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
    <p class="social">
        <?= e($post['like_count'] ?? 0) ?> likes
        &middot;
        <a href="/posts/show?id=<?= e($post['id']) ?>#comments"><?= e($post['comment_count'] ?? 0) ?> comments</a>
        <?php if ($viewer && is_verified($viewer) && $post['status'] === 'published'): ?>
    <form method="post" action="/posts/like">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= e($post['id']) ?>">
        <input type="hidden" name="back" value="<?= e($_SERVER['REQUEST_URI'] ?? '/posts') ?>">
        <button type="submit"><?= !empty($post['liked']) ? 'Unlike' : 'Like' ?></button>
    </form>
<?php endif; ?>
</p>
<?php if ($viewer && is_verified($viewer) && (int) $post['user_id'] !== (int) $viewer['id']): ?>
    <form method="post" action="/reports/create" class="report">
        <?= csrf_field() ?>
        <input type="hidden" name="target_type" value="post">
        <input type="hidden" name="id" value="<?= e($post['id']) ?>">
        <input type="hidden" name="back" value="<?= e($_SERVER['REQUEST_URI'] ?? '/posts') ?>">
        <input type="text" name="reason" maxlength="255" placeholder="Report this post: why?">
        <button type="submit">Report</button>
    </form>
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