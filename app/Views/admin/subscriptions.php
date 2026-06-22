<div class="dash-header-premium">
  <div>
    <h1>Subscriptions</h1>
    <p>Manage user subscription plans</p>
  </div>
  <a href="/admin/subscriptions/add" class="dash-btn dash-btn-primary"><i class="fas fa-plus"></i> Add Subscription</a>
</div>

<?php if (!empty($subscriptions)): ?>
<div class="dash-table-wrapper">
  <table class="dash-table">
    <thead>
      <tr><th>User</th><th>Email</th><th>Plan</th><th>Status</th><th>Started</th><th>Ends</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach ($subscriptions as $s): ?>
        <tr>
          <td><?= htmlspecialchars($s['user_name']) ?></td>
          <td><?= htmlspecialchars($s['user_email']) ?></td>
          <td><span class="status-badge status-badge-completed"><?= ucfirst(htmlspecialchars($s['plan'])) ?></span></td>
          <td><span class="status-badge status-badge-<?= htmlspecialchars($s['status']) ?>"><?= ucfirst(htmlspecialchars($s['status'])) ?></span></td>
          <td><?= htmlspecialchars($s['started_at']) ?></td>
          <td><?= htmlspecialchars($s['ends_at'] ?? '-') ?></td>
          <td>
            <a href="/admin/subscriptions/<?= (int)$s['id'] ?>/edit" class="dash-btn dash-btn-sm dash-btn-outline">Edit</a>
            <form method="POST" action="/admin/subscriptions/<?= (int)$s['id'] ?>/delete" class="d-inline-flex" onsubmit="return confirm('Delete this subscription?')">
              <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
              <button type="submit" class="dash-btn dash-btn-sm dash-btn-danger">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
<div class="dash-empty-state"><h3>No subscriptions</h3><p>No subscriptions have been created yet.</p></div>
<?php endif; ?>
