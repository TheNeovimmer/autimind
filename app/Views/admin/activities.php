<div class="dash-header-premium">
  <div>
    <h1>Activities</h1>
    <p>Manage children's educational activities</p>
  </div>
  <a href="/admin/activities/add" class="dash-btn dash-btn-primary"><i class="fas fa-plus"></i> Add Activity</a>
</div>

<?php if (!empty($activities)): ?>
<div class="dash-table-wrapper">
  <table class="dash-table">
    <thead>
      <tr><th>Title</th><th>Category</th><th>Difficulty</th><th>Active</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach ($activities as $a): ?>
        <tr>
          <td><strong><?= htmlspecialchars($a['title']) ?></strong></td>
          <td><span class="status-badge status-badge-completed"><?= htmlspecialchars($a['category']) ?></span></td>
          <td><?= htmlspecialchars($a['difficulty']) ?></td>
          <td><?= $a['is_active'] ? '<span class="status-badge status-badge-active">Yes</span>' : '<span class="status-badge status-badge-cancelled">No</span>' ?></td>
          <td>
            <a href="/admin/activities/<?= (int)$a['id'] ?>/edit" class="dash-btn dash-btn-sm dash-btn-outline">Edit</a>
            <form method="POST" action="/admin/activities/<?= (int)$a['id'] ?>/delete" class="d-inline-flex" onsubmit="return confirm('Delete this activity?');">
              <input type="hidden" name="_csrf_token" value="<?= \App\Core\Session::csrf_token() ?>">
              <button type="submit" class="dash-btn dash-btn-sm dash-btn-danger">Delete</button>
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
