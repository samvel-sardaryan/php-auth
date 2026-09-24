<?php
// One comment. Needs $comment, $post and $viewer in scope (views/post.php sets them).
// Buttons are a convenience only: the handlers enforce every rule themselves.
$canAct = $viewer && is_verified($viewer) && $comment['deleted_at'] === null;
$isMine = $canAct && (int) $comment['user_id'] === (int) $viewer['id'];
$canDelete = $canAct && ($isMine || (int) $post['user_id'] === (int) $viewer['id'] || can('moderate_comments'));
$canReply = $canAct && $comment['parent_id'] === null && $post['status'] === 'published';
?>
<div class="comment" id="comment-<?= e($comment['id']) ?>">
    <?php if ($comment['deleted_at'] !== null): ?>
        <p class="meta">[deleted]</p>
    <?php else: ?>
        <p class="meta">
            <a href="/users?id=<?= e($comment['user_id']) ?>"><?= e($comment['author_name']) ?></a>
            on <?= e($comment['created_at']) ?>
        </p>
        <div class="content"><?= nl2br(e($comment['content'])) ?></div>

        <?php if ($canAct && !$isMine): ?>
            <form method="post" action="/reports/create" class="report">
                <?= csrf_field() ?>
                <input type="hidden" name="target_type" value="comment">
                <input type="hidden" name="id" value="<?= e($comment['id']) ?>">
                <input type="hidden" name="back" value="<?= e($_SERVER['REQUEST_URI'] ?? '/posts') ?>">
                <input type="text" name="reason" maxlength="255" placeholder="Report this comment: why?">
                <button type="submit">Report</button>
            </form>
        <?php endif; ?>
        <?php if ($isMine || $canDelete || $canReply): ?>
            <p class="actions">
                <?php if ($canReply): ?>
                    <a href="/posts/show?id=<?= e($post['id']) ?>&reply_to=<?= e($comment['id']) ?>#comment-form">Reply</a>
                <?php endif; ?>
                <?php if ($isMine): ?>
                    <a href="/comments/edit?id=<?= e($comment['id']) ?>">Edit</a>
                <?php endif; ?>
                <?php if ($canDelete): ?>
                    <form method="post" action="/comments/delete" onsubmit="return confirm('Delete this comment?')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= e($comment['id']) ?>">
                        <button type="submit">Delete</button>
                    </form>
                <?php endif; ?>
            </p>
        <?php endif; ?>
    <?php endif; ?>
</div>
