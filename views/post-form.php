<?php $isEdit = $post !== null; ?>

<h1><?= $isEdit ? 'Edit post' : 'New post' ?></h1>

<form method="post" action="<?= $isEdit ? '/posts/edit' : '/posts/create' ?>" novalidate>
    <?= csrf_field() ?>
    <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= e($post['id']) ?>">
    <?php endif; ?>
    <div>
        <label for="title">Title</label>
        <input type="text" id="title" name="title" value="<?= e($old['title'] ?? '') ?>">
        <?php if (isset($errors['title'])): ?>
            <span class="error"><?= e($errors['title']) ?></span>
        <?php endif; ?>
    </div>
    <div>
        <label for="content">Content</label>
        <textarea id="content" name="content" rows="10" cols="60"><?= e($old['content'] ?? '') ?></textarea>
        <?php if (isset($errors['content'])): ?>
            <span class="error"><?= e($errors['content']) ?></span>
        <?php endif; ?>
    </div>
    <button type="submit"><?= $isEdit ? 'Save changes' : 'Publish' ?></button>
</form>

<a href="/posts">Back to posts</a>
