<div class="dash-header">
  <div>
    <h1>Contact Messages</h1>
    <p>Messages from the contact form</p>
  </div>
</div>

<?php if (!empty($contacts)): ?>
<div class="table-responsive">
  <table class="dash-table">
    <thead>
      <tr><th>Name</th><th>Email</th><th>Subject</th><th>Message</th><th>Status</th><th>Date</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach ($contacts as $c): ?>
        <tr>
          <td><strong><?= htmlspecialchars($c['name']) ?></strong></td>
          <td><?= htmlspecialchars($c['email']) ?></td>
          <td><?= htmlspecialchars($c['subject']) ?></td>
          <td><?= htmlspecialchars(substr($c['message'], 0, 80)) ?></td>
          <td><?= $c['is_read'] ? '<span class="status-active">Read</span>' : '<span class="status-pending">New</span>' ?></td>
          <td><?= htmlspecialchars($c['created_at']) ?></td>
          <td>
            <?php if (!$c['is_read']): ?>
              <form method="POST" action="/admin/contacts/<?= (int)$c['id'] ?>/read" style="display:inline;">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <button type="submit" class="btn-sm btn-outline">Mark Read</button>
              </form>
            <?php endif; ?>
            <form method="POST" action="/admin/contacts/<?= (int)$c['id'] ?>/delete" style="display:inline;" onsubmit="return confirm('Delete this message?')">
              <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
              <button type="submit" class="btn-sm btn-outline btn-danger">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
<div class="dash-empty-state"><h3>No messages</h3><p>No contact form submissions yet.</p></div>
<?php endif; ?>
