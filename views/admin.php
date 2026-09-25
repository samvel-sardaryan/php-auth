<?php $title = 'Admin'; ?>
<?php require __DIR__ . '/_header.php'; ?>

<h1>Admin Page</h1>

<ul>
    <li><a href="/admin/users">Users</a></li>
    <li><a href="/admin/categories">Categories</a></li>
    <li><a href="/admin/deleted-posts">Deleted posts</a></li>
    <li><a href="/admin/activity">Activity log</a></li>
</ul>

<a href="/dashboard">Back to dashboard</a>

<?php require __DIR__ . '/_footer.php'; ?>