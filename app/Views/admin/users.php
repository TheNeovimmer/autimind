<div class="dash-header">
  <div>
    <h1>Users</h1>
    <p>Manage all platform users (<?= (int)($totalUsers ?? 0) ?>)</p>
  </div>
  <a href="/admin/users/add" class="dash-btn dash-btn-primary"><i class="fas fa-plus"></i> Add User</a>
</div>

<div class="card mb-2">
  <form method="GET" class="d-flex gap-1 flex-wrap align-items-center">
    <div class="mb-3 flex-1" style="min-width:200px;margin:0;">
      <label for="search" class="form-label">Search</label>
      <input type="text" id="search" name="search" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Name or email..." class="form-control">
    </div>
    <div class="mb-3" style="margin:0;">
      <label for="role" class="form-label">Role</label>
      <select id="role" name="role" class="form-select">
        <option value="">All Roles</option>
        <option value="parent" <?= ($role ?? '') === 'parent' ? 'selected' : '' ?>>Parent</option>
        <option value="specialist" <?= ($role ?? '') === 'specialist' ? 'selected' : '' ?>>Specialist</option>
        <option value="admin" <?= ($role ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
      </select>
    </div>
    <button type="submit" class="dash-btn dash-btn-primary" style="margin-bottom:0;">Filter</button>
    <?php if (!empty($search) || !empty($role)): ?>
      <a href="/admin/users" class="dash-btn dash-btn-outline" style="margin-bottom:0;">Clear</a>
    <?php endif; ?>
  </form>
</div>

<?php if (!empty($users)): ?>
<div class="table-responsive">
  <table class="table table-hover align-middle mb-0 small">
    <thead>
      <tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach ($users as $u): ?>
        <tr>
          <td><strong><?= htmlspecialchars($u['name']) ?></strong></td>
          <td><?= htmlspecialchars($u['email']) ?></td>
          <td><span class="badge bg-primary-subtle text-primary-emphasis role-<?= htmlspecialchars($u['role']) ?>"><?= ucfirst(htmlspecialchars($u['role'])) ?></span></td>
          <td><?= $u['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>' ?></td>
          <td><?= htmlspecialchars($u['created_at']) ?></td>
          <td>
            <a href="/admin/users/<?= (int)$u['id'] ?>/edit" class="dash-btn dash-btn-sm dash-btn-outline">Edit</a>
            <?php if ((int)$u['id'] !== (int)\App\Core\Session::get('user_id')): ?>
            <form method="POST" action="/admin/users/<?= (int)$u['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Delete this user?');">
              <input type="hidden" name="_csrf_token" value="<?= \App\Core\Session::csrf_token() ?>">
              <button type="submit" class="dash-btn dash-btn-sm dash-btn-danger">Delete</button>
            </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php if (($totalPages ?? 1) > 1): ?>
<nav aria-label="User list pagination">
  <ul class="pagination pagination-sm justify-content-center mb-0">
    <?php if (($page ?? 1) > 1): ?>
      <li class="page-item">
        <a class="page-link" href="?page=<?= (int)$page - 1 ?>&search=<?= urlencode($search ?? '') ?>&role=<?= urlencode($role ?? '') ?>">Previous</a>
      </li>
    <?php endif; ?>
    <?php for ($p = 1; $p <= ($totalPages ?? 1); $p++): ?>
      <li class="page-item <?= ($page ?? 1) === $p ? 'active' : '' ?>">
        <a class="page-link" href="?page=<?= $p ?>&search=<?= urlencode($search ?? '') ?>&role=<?= urlencode($role ?? '') ?>"><?= $p ?></a>
      </li>
    <?php endfor; ?>
    <?php if (($page ?? 1) < ($totalPages ?? 1)): ?>
      <li class="page-item">
        <a class="page-link" href="?page=<?= (int)$page + 1 ?>&search=<?= urlencode($search ?? '') ?>&role=<?= urlencode($role ?? '') ?>">Next</a>
      </li>
    <?php endif; ?>
  </ul>
</nav>
<?php endif; ?>

<?php else: ?>
<div class="dash-empty-state"><h3>No users</h3><p>No users match your criteria.</p></div>
<?php endif; ?>
