<div class="dash-header-premium">
  <div>
    <h1>Messages</h1>
    <p>All platform messages</p>
  </div>
</div>

<?php if (!empty($messages)): ?>
<div class="dash-table-wrapper">
  <table class="dash-table">
    <thead>
      <tr><th>From</th><th>To</th><th>Subject</th><th>Body</th><th>Status</th><th>Date</th></tr>
    </thead>
    <tbody>
      <?php foreach ($messages as $m): ?>
        <tr>
          <td><?= htmlspecialchars($m['sender_name']) ?></td>
          <td><?= htmlspecialchars($m['receiver_name']) ?></td>
          <td><?= htmlspecialchars($m['subject']) ?></td>
          <td><?= htmlspecialchars(substr($m['body'], 0, 80)) ?></td>
          <td><?= $m['is_read'] ? '<span class="status-badge status-badge-active">Read</span>' : '<span class="status-badge status-badge-pending">Unread</span>' ?></td>
          <td><?= htmlspecialchars($m['created_at']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
<div class="dash-empty-state"><h3>No messages</h3><p>No messages have been sent yet.</p></div>
<?php endif; ?>
