<?php $isEdit = $post !== null; ?>
<?php $title = $isEdit ? 'Edit post' : 'New post'; ?>
<?php require __DIR__ . '/_header.php'; ?>

<h1><?= $isEdit ? 'Edit post' : 'New post' ?></h1>

<?php if ($error): ?>
    <p class="error"><?= e($error) ?></p>
<?php endif; ?>

<form method="post" action="<?= $isEdit ? '/posts/edit' : '/posts/create' ?>" enctype="multipart/form-data" novalidate>
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
    <div>
        <label for="tags">Tags</label>
        <input type="text" id="tags" name="tags" value="<?= e($old['tags'] ?? '') ?>">
        <p class="hint">Comma separated, up to 5. Lowercased automatically.</p>
        <?php if (isset($errors['tags'])): ?>
            <span class="error"><?= e($errors['tags']) ?></span>
        <?php endif; ?>
    </div>
    <div>
        <label for="category_id">Category</label>
        <select id="category_id" name="category_id">
            <option value="">Choose a category</option>
            <?php foreach ($categories as $c): ?>
                <option value="<?= e($c['id']) ?>" <?= (string) ($old['category_id'] ?? '') === (string) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (isset($errors['category_id'])): ?>
            <span class="error"><?= e($errors['category_id']) ?></span>
        <?php endif; ?>
    </div>
    <div>
        <label for="status">Status</label>
        <select id="status" name="status">
            <?php foreach ($isEdit ? STATUS_TYPES : ['draft', 'published'] as $s): ?>
                <option value="<?= e($s) ?>" <?= ($old['status'] ?? 'draft') === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (isset($errors['status'])): ?>
            <span class="error"><?= e($errors['status']) ?></span>
        <?php endif; ?>
        <p class="hint">A draft is visible only to you. Archived posts stay off the feed.</p>
    </div>
    <?php if (!empty($images)): ?>
        <div>
            <p>Current images</p>
            <?php foreach ($images as $image): ?>
                <label class="image-choice">
                    <img src="/uploads/posts/<?= e($image['path']) ?>" alt="" width="120">
                    <input type="checkbox" name="remove_images[]" value="<?= e($image['id']) ?>"> Remove
                </label>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <div>
        <label for="images"><?= $isEdit ? 'Add images' : 'Images' ?></label>
        <input type="file" id="images" name="images[]" accept="image/*" multiple>
        <p class="hint">JPG, PNG, GIF or WEBP, up to 2MB each, <?= e(POST_IMAGE_MAX_COUNT) ?> in total.<?= $isEdit ? ' If saving fails, choose the files again.' : '' ?></p>
        <?php if (isset($errors['images'])): ?>
            <span class="error"><?= e($errors['images']) ?></span>
        <?php endif; ?>
    </div>
    <button type="submit"><?= $isEdit ? 'Save changes' : 'Publish' ?></button>
</form>

<a href="/posts">Back to posts</a>

<?php require __DIR__ . '/_footer.php'; ?>