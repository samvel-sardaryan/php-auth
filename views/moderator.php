<?php $title = 'Moderation'; ?>
<?php require __DIR__ . '/_header.php'; ?>

<h1>Moderation queue</h1>

<?php if ($success): ?>
    <p class="success"><?= e($success) ?></p>
<?php endif; ?>
<?php if ($error): ?>
    <p class="error"><?= e($error) ?></p>
<?php endif; ?>

<?php if (empty($reports)): ?>
    <p class="hint">Nothing has been reported.</p>
<?php else: ?>
    <table>
        <tr>
            <th>Reported</th>
            <th>Type</th>
            <th>Target</th>
            <th>Reason</th>
            <th>By</th>
            <th>Action</th>
        </tr>
        <?php foreach ($reports as $report): ?>
            <?php
            $target = $report['target'] ?? null;
            $isPost = $report['target_type'] === 'post';
            $link = $target === null ? null
                : ($isPost
                    ? '/posts/show?id=' . $target['id']
                    : '/posts/show?id=' . $target['post_id'] . '#comment-' . $target['id']);
            ?>
            <tr>
                <td><?= e($report['created_at']) ?></td>
                <td><?= e($report['target_type']) ?></td>
                <td>
                    <?php if ($target === null): ?>
                        <span class="hint">No longer exists</span>
                    <?php else: ?>
                        <a href="<?= e($link) ?>">
                            <?= e($isPost
                                ? $target['title']
                                : mb_strimwidth($target['content'], 0, 60, '…')) ?>
                        </a>
                        <span class="meta">by <?= e($target['author_name'] ?? 'unknown') ?></span>
                        <?php if ($target['deleted_at'] !== null): ?>
                            <span class="status-badge">HIDDEN</span>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
                <td><?= e($report['reason']) ?></td>
                <td><?= e($report['reporter_name']) ?></td>
                <td>
                    <?php
                    $may = $isPost ? can('moderate_posts') : can('moderate_comments');
                    ?>
                    <?php if ($target !== null && $target['deleted_at'] === null && $may): ?>
                        <form method="post" action="/moderation/hide-<?= $isPost ? 'post' : 'comment' ?>"
                            onsubmit="return confirm('Hide this <?= $isPost ? 'post' : 'comment' ?>?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= e($target['id']) ?>">
                            <button type="submit">Hide</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<a href="/dashboard">Back to dashboard</a>

<?php require __DIR__ . '/_footer.php'; ?>