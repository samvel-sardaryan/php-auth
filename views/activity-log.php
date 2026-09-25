<?php $title = 'Activity Log'; ?>
<?php require __DIR__ . '/_header.php'; ?>

<h1>Activity log</h1>

<?php if ($success): ?>
    <p class="success"><?= e($success) ?></p>
<?php endif; ?>
<?php if ($error): ?>
    <p class="error"><?= e($error) ?></p>
<?php endif; ?>

<!-- GET, so a filtered log is a shareable URL and needs no CSRF token -->
<form method="get" action="/admin/activity" class="filters">
    <label for="user_id">User</label>
    <select id="user_id" name="user_id">
        <option value="">Everyone</option>
        <?php foreach ($users as $u): ?>
            <option value="<?= e($u['id']) ?>" <?= (int) $filterUserId === (int) $u['id'] ? 'selected' : '' ?>>
                <?= e($u['name']) ?> (<?= e($u['email']) ?>)
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Filter</button>
    <a href="/admin/activity">Clear</a>
</form>

<?php if (empty($activity)): ?>
    <p class="hint">Nothing has been logged yet.</p>
<?php else: ?>
    <table>
        <tr>
            <th>When</th>
            <th>Who</th>
            <th>Action</th>
            <th>Target</th>
            <th>IP</th>
        </tr>
        <?php foreach ($activity as $row): ?>
            <?php
            $link = null;
            if ($row['target_type'] === 'post' && $row['target_id'] !== null) {
                $link = '/posts/show?id=' . $row['target_id'];
            } elseif ($row['target_type'] === 'user' && $row['target_id'] !== null) {
                $link = '/users?id=' . $row['target_id'];
            }
            ?>
            <tr>
                <td><?= e($row['created_at']) ?></td>
                <td>
                    <?php if ($row['user_id'] === null): ?>
                        <span class="hint">deleted user</span>
                    <?php else: ?>
                        <a href="/users?id=<?= e($row['user_id']) ?>"><?= e($row['user_name'] ?? ('#' . $row['user_id'])) ?></a>
                    <?php endif; ?>
                </td>
                <td><?= e($row['action']) ?></td>
                <td>
                    <?php if ($row['target_type'] === null): ?>
                        <span class="hint">&mdash;</span>
                    <?php elseif ($link !== null): ?>
                        <a href="<?= e($link) ?>"><?= e($row['target_type']) ?> #<?= e($row['target_id']) ?></a>
                    <?php else: ?>
                        <?= e($row['target_type']) ?><?= $row['target_id'] !== null ? ' #' . e($row['target_id']) : '' ?>
                    <?php endif; ?>
                </td>
                <td><?= e($row['ip_address'] ?? '') ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <?php $qs = fn($n) => '/admin/activity?' . http_build_query(array_filter(['user_id' => $filterUserId, 'page' => $n])); ?>
    <nav class="pagination">
        <?php if ($page > 1): ?>
            <a href="<?= e($qs($page - 1)) ?>">Previous</a>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $pages; $i++): ?>
            <?php if ($i === $page): ?>
                <strong><?= e($i) ?></strong>
            <?php else: ?>
                <a href="<?= e($qs($i)) ?>"><?= e($i) ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        <?php if ($page < $pages): ?>
            <a href="<?= e($qs($page + 1)) ?>">Next</a>
        <?php endif; ?>
    </nav>

    <p class="count"><?= e($total) ?> <?= (int) $total === 1 ? 'entry' : 'entries' ?>, page <?= e($page) ?> of <?= e($pages) ?></p>
<?php endif; ?>

<a href="/admin">Back to admin</a>

<?php require __DIR__ . '/_footer.php'; ?>