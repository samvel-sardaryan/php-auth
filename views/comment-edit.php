<h1>Edit comment</h1>

<?php if ($error): ?>
    <p class="error"><?= e($error) ?></p>
<?php endif; ?>

<form method="post" action="/comments/edit" novalidate>
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= e($comment['id']) ?>">
    <div>
        <label for="content">Comment</label>
        <textarea id="content" name="content" rows="4" cols="60"><?= e($old['content'] ?? '') ?></textarea>
        <?php if (isset($errors['content'])): ?>
            <span class="error"><?= e($errors['content']) ?></span>
        <?php endif; ?>
    </div>
    <button type="submit">Save</button>
</form>

<a href="/posts/show?id=<?= e($comment['post_id']) ?>#comment-<?= e($comment['id']) ?>">Cancel</a>
