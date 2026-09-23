<?php $viewer = current_user(); ?>

<style>
    .comment {
        padding: .4rem 0 .4rem .6rem;
        border-left: 3px solid #d8d8d8;
        margin: .6rem 0;
    }

    .comment .meta {
        color: #666;
        font-size: .9em;
        margin: 0 0 .3rem;
    }

    .comment .actions {
        margin: .3rem 0 0;
        font-size: .9em;
    }

    .comment .actions form {
        display: inline;
    }

    .replies {
        margin-left: 2rem;
        border-left: 2px dashed #ccc;
        padding-left: .8rem;
    }

    .replies .comment {
        border-left-color: #e8e8e8;
    }
</style>

<?php if ($success): ?>
    <p class="success"><?= e($success) ?></p>
<?php endif; ?>
<?php if ($error): ?>
    <p class="error"><?= e($error) ?></p>
<?php endif; ?>

<?php require __DIR__ . '/_post.php'; ?>

<section class="comments" id="comments">
    <h2>Comments</h2>

    <?php if (empty($comments['top'])): ?>
        <p>No comments yet.</p>
    <?php endif; ?>

    <?php foreach ($comments['top'] ?? [] as $comment): ?>
        <?php require __DIR__ . '/_comment.php'; ?>
        <?php if (!empty($comments['replies'][$comment['id']])): ?>
            <div class="replies">
                <?php foreach ($comments['replies'][$comment['id']] as $comment): ?>
                    <?php require __DIR__ . '/_comment.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>

    <?php if ($post['status'] !== 'published'): ?>
        <p class="hint">Comments are closed on <?= e($post['status']) ?> posts.</p>
    <?php elseif (!$viewer): ?>
        <p><a href="/login">Log in</a> to comment.</p>
    <?php elseif (!is_verified($viewer)): ?>
        <p><a href="/verify-notice">Verify your email</a> to comment.</p>
    <?php else: ?>
        <form method="post" action="/comments/create" id="comment-form" novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="post_id" value="<?= e($post['id']) ?>">
            <?php if (!empty($replyTo)): ?>
                <input type="hidden" name="parent_id" value="<?= e($replyTo) ?>">
                <p class="hint">
                    Replying to a comment.
                    <a href="/posts/show?id=<?= e($post['id']) ?>#comment-form">Cancel</a>
                </p>
            <?php endif; ?>
            <div>
                <label for="content"><?= !empty($replyTo) ? 'Your reply' : 'Add a comment' ?></label>
                <textarea id="content" name="content" rows="4" cols="60"><?= e($old['content'] ?? '') ?></textarea>
                <?php if (isset($errors['content'])): ?>
                    <span class="error"><?= e($errors['content']) ?></span>
                <?php endif; ?>
            </div>
            <button type="submit"><?= !empty($replyTo) ? 'Reply' : 'Comment' ?></button>
        </form>
    <?php endif; ?>
</section>

<a href="/posts">Back to posts</a>