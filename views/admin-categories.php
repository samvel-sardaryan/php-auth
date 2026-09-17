<?php $counts = $counts ?? []; ?>

<h1>Categories</h1>

<?php if ($success): ?>
    <p class="success"><?= e($success) ?></p>
<?php endif; ?>
<?php if ($error): ?>
    <p class="error"><?= e($error) ?></p>
<?php endif; ?>

<table border="1">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Slug</th>
            <th>Posts</th>
            <th>Rename</th>
            <th>Delete</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($categories as $category): ?>
            <tr>
                <td><?= e($category['id']) ?></td>
                <td>
                    <?= e($category['name']) ?>
                    <?php if ($category['is_default']): ?>
                        <span class="hint">(default)</span>
                    <?php endif; ?>
                </td>
                <td><?= e($category['slug']) ?></td>
                <td><?= e($counts[$category['id']] ?? 0) ?></td>
                <td>
                    <form method="post" action="/admin/categories/rename" novalidate>
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= e($category['id']) ?>">
                        <input type="text" name="name" value="<?= e($category['name']) ?>">
                        <input type="text" name="slug" value="<?= e($category['slug']) ?>">
                        <button type="submit">Rename</button>
                    </form>
                </td>
                <td>
                    <?php if ($category['is_default']): ?>
                        <span class="hint">Cannot be deleted</span>
                    <?php else: ?>
                        <form method="post" action="/admin/categories/delete"
                              onsubmit="return confirm('Delete this category? Its posts move to the default category.')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= e($category['id']) ?>">
                            <button type="submit">Delete</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h2>New category</h2>
<form method="post" action="/admin/categories/create" novalidate>
    <?= csrf_field() ?>
    <div>
        <label for="name">Name</label>
        <input type="text" id="name" name="name" value="<?= e($old['name'] ?? '') ?>">
        <?php if (isset($errors['name'])): ?>
            <span class="error"><?= e($errors['name']) ?></span>
        <?php endif; ?>
    </div>
    <div>
        <label for="slug">Slug</label>
        <input type="text" id="slug" name="slug" value="<?= e($old['slug'] ?? '') ?>">
        <p class="hint">Leave empty to generate it from the name.</p>
        <?php if (isset($errors['slug'])): ?>
            <span class="error"><?= e($errors['slug']) ?></span>
        <?php endif; ?>
    </div>
    <button type="submit">Create</button>
</form>

<a href="/admin">Back to admin</a>
