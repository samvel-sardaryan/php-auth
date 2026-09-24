<h1>Deleted posts</h1>

<?php if ($success): ?>
    <p class="success"><?= e($success) ?></p>
<?php endif; ?>
<?php if ($error): ?>
    <p class="error"><?= e($error) ?></p>
<?php endif; ?>

<p class="hint">
    Deleted posts are hidden everywhere, including from their author. Their images are kept on disk,
    so restoring a post brings them back.
</p>

<?php if (empty($posts)): ?>
    <p class="hint">Nothing has been deleted.</p>
<?php else: ?>
    <table>
        <tr>
            <th>Id</th>
            <th>Title</th>
            <th>Author</th>
            <th>Status</th>
            <th>Deleted</th>
            <th>Action</th>
        </tr>
        <?php foreach ($posts as $post): ?>
            <tr>
                <td><?= e($post['id']) ?></td>
                <td><?= e($post['title']) ?></td>
                <td><a href="/users?id=<?= e($post['user_id']) ?>"><?= e($post['author_name']) ?></a></td>
                <td>
                    <?= e($post['status']) ?>
                    <?php if ($post['status'] !== 'published'): ?>
                        <span class="hint">(stays off the feed when restored)</span>
                    <?php endif; ?>
                </td>
                <td><?= e($post['deleted_at']) ?></td>
                <td>
                    <form method="post" action="/admin/deleted-posts/restore">
                        <?= csrf_field() ?>
                        <input type="hidden" name="post_id" value="<?= e($post['id']) ?>">
                        <button type="submit">Restore</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<a href="/admin">Back to admin</a>
