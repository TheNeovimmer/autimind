<div class="dash-header">
  <div>
    <h1>Activities</h1>
    <p>Manage children's educational activities</p>
  </div>
  <a href="/admin/activities/add" class="btn-primary"><i class="fas fa-plus"></i> Add Activity</a>
</div>

<?php if (!empty($activities)): ?>
<div class="table-responsive">
  <table class="dash-table">
    <thead>
      <tr><th>Title</th><th>Category</th><th>Difficulty</th><th>Active</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach ($activities as $a): ?>
        <tr>
          <td><strong><?= htmlspecialchars($a['title']) ?></strong></td>
          <td><span class="role-badge"><?= htmlspecialchars($a['category']) ?></span></td>
          <td><?= htmlspecialchars($a['difficulty']) ?></td>
          <td><?= $a['is_active'] ? '<span class="status-active">Yes</span>' : '<span class="status-cancelled">No</span>' ?></td>
          <td>
            <a href="/admin/activities/<?= (int)$a['id'] ?>/edit" class="btn-sm btn-outline">Edit</a>
            <form method="POST" action="/admin/activities/<?= (int)$a['id'] ?>/delete" style="display:inline;" onsubmit="return confirm('Delete this activity?');">
              <input type="hidden" name="_csrf_token" value="<?= \App\Core\Session::csrf_token() ?>">
              <button type="submit" class="btn-sm" style="background:#fee2e2;color:#dc2626;border:none;cursor:pointer;">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
<div class="dash-empty-state"><h3>No activities</h3><p>Add activities for children to enjoy.</p></div>
<?php endif; ?>
