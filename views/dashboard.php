<h1>Dashboard</h1>
<div>
    <p><span class="label">Hello</span> <?= e($user['name']) ?></p>
    <p><span class="label">Email:</span> <?= e($user['email']) ?></p>
    <p><span class="label">Created At:</span> <?= e($user['created_at']) ?></p>
    <form action="/logout" method="POST">
        <button type="submit">Logout</button>
    </form>
</div>