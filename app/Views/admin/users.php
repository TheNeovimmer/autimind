<div class="dash-header">
  <div>
    <h1>Users</h1>
    <p>Manage all platform users (<?= (int)($totalUsers ?? 0) ?>)</p>
  </div>
  <a href="/admin/users/add" class="btn-primary"><i class="fas fa-plus"></i> Add User</a>
</div>

<div class="dash-card" style="margin-bottom:1rem;">
  <form method="GET" class="dash-form" style="display:flex;gap:0.75rem;align-items:flex-end;flex-wrap:wrap;">
    <div class="form-group" style="margin:0;flex:1;min-width:200px;">
      <label for="search">Search</label>
      <input type="text" id="search" name="search" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Name or email...">
    </div>
    <div class="form-group" style="margin:0;">
      <label for="role">Role</label>
      <select id="role" name="role">
        <option value="">All Roles</option>
        <option value="parent" <?= ($role ?? '') === 'parent' ? 'selected' : '' ?>>Parent</option>
        <option value="specialist" <?= ($role ?? '') === 'specialist' ? 'selected' : '' ?>>Specialist</option>
        <option value="admin" <?= ($role ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
      </select>
    </div>
    <button type="submit" class="btn-primary" style="margin-bottom:0;">Filter</button>
    <?php if (!empty($search) || !empty($role)): ?>
      <a href="/admin/users" class="btn-outline" style="margin-bottom:0;">Clear</a>
    <?php endif; ?>
  </form>
</div>

<?php if (!empty($users)): ?>
<div class="table-responsive">
  <table class="dash-table">
    <thead>
      <tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach ($users as $u): ?>
        <tr>
          <td><strong><?= htmlspecialchars($u['name']) ?></strong></td>
          <td><?= htmlspecialchars($u['email']) ?></td>
          <td><span class="role-badge role-<?= htmlspecialchars($u['role']) ?>"><?= ucfirst(htmlspecialchars($u['role'])) ?></span></td>
          <td><?= $u['is_active'] ? '<span class="status-active">Active</span>' : '<span class="status-cancelled">Inactive</span>' ?></td>
          <td><?= htmlspecialchars($u['created_at']) ?></td>
          <td>
            <a href="/admin/users/<?= (int)$u['id'] ?>/edit" class="btn-sm btn-outline">Edit</a>
            <?php if ((int)$u['id'] !== (int)\App\Core\Session::get('user_id')): ?>
            <form method="POST" action="/admin/users/<?= (int)$u['id'] ?>/delete" style="display:inline;" onsubmit="return confirm('Delete this user?');">
              <input type="hidden" name="_csrf_token" value="<?= \App\Core\Session::csrf_token() ?>">
              <button type="submit" class="btn-sm" style="background:#fee2e2;color:#dc2626;border:none;cursor:pointer;">Delete</button>
            </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php if (($totalPages ?? 1) > 1): ?>
<div style="display:flex;justify-content:center;align-items:center;gap:0.5rem;margin-top:1rem;">
  <?php if (($page ?? 1) > 1): ?>
    <a href="?page=<?= (int)$page - 1 ?>&search=<?= urlencode($search ?? '') ?>&role=<?= urlencode($role ?? '') ?>" class="btn-sm btn-outline">Previous</a>
  <?php endif; ?>
  <?php for ($p = 1; $p <= ($totalPages ?? 1); $p++): ?>
    <a href="?page=<?= $p ?>&search=<?= urlencode($search ?? '') ?>&role=<?= urlencode($role ?? '') ?>" style="padding:0.4rem 0.7rem;text-decoration:none;border-radius:6px;<?= ($page ?? 1) === $p ? 'background:var(--primary);color:#fff;' : 'background:var(--card-bg);color:var(--text);' ?>"><?= $p ?></a>
  <?php endfor; ?>
  <?php if (($page ?? 1) < ($totalPages ?? 1)): ?>
    <a href="?page=<?= (int)$page + 1 ?>&search=<?= urlencode($search ?? '') ?>&role=<?= urlencode($role ?? '') ?>" class="btn-sm btn-outline">Next</a>
  <?php endif; ?>
</div>
<?php endif; ?>

<?php else: ?>
<div class="dash-empty-state"><h3>No users</h3><p>No users match your criteria.</p></div>
<?php endif; ?>
