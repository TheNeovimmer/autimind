<div class="dash-header">
  <div>
    <h1>Specialists</h1>
    <p>Manage specialist accounts and approvals</p>
  </div>
</div>

<?php if (!empty($specialists)): ?>
<div class="table-responsive">
  <table class="table table-hover align-middle mb-0 small">
    <thead>
      <tr><th>Name</th><th>Email</th><th>Title</th><th>Specializations</th><th>Experience</th><th>Available</th><th>Active</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach ($specialists as $s): ?>
        <tr>
          <td><strong><?= htmlspecialchars($s['name']) ?></strong></td>
          <td><?= htmlspecialchars($s['email']) ?></td>
          <td><?= htmlspecialchars($s['title'] ?? '-') ?></td>
          <td><?= htmlspecialchars($s['specializations'] ?? '-') ?></td>
          <td><?= $s['years_experience'] ? (int)$s['years_experience'] . ' yrs' : '-' ?></td>
          <td><?= $s['is_available'] ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>' ?></td>
          <td><?= $s['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>' ?></td>
          <td>
            <form method="POST" action="/admin/specialists/<?= (int)$s['id'] ?>/approve" class="d-inline">
              <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
              <input type="hidden" name="is_active" value="<?= $s['is_active'] ? 0 : 1 ?>">
              <button type="submit" class="dash-btn dash-btn-sm dash-btn-outline">
                <?= $s['is_active'] ? 'Deactivate' : 'Activate' ?>
              </button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
<div class="dash-empty-state"><h3>No specialists</h3><p>No specialist accounts found.</p></div>
<?php endif; ?>
