<div class="dash-header">
  <div>
    <h1>Chatbot Responses</h1>
    <p>Manage automated chatbot responses</p>
  </div>
  <a href="/admin/chatbot/add" class="dash-btn dash-btn-primary"><i class="fas fa-plus"></i> Add Response</a>
</div>

<?php if (!empty($responses)): ?>
<div class="table-responsive">
  <table class="table table-hover align-middle mb-0 small">
    <thead>
      <tr><th>Keywords</th><th>Response</th><th>Category</th><th>Active</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach ($responses as $r): ?>
        <tr>
          <td><?= htmlspecialchars($r['keywords']) ?></td>
          <td><?= htmlspecialchars(substr($r['response_text'], 0, 80)) ?><?= strlen($r['response_text']) > 80 ? '...' : '' ?></td>
          <td><?= htmlspecialchars($r['category'] ?? '-') ?></td>
          <td><?= $r['is_active'] ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>' ?></td>
          <td>
            <a href="/admin/chatbot/<?= (int)$r['id'] ?>/edit" class="dash-btn dash-btn-sm dash-btn-outline">Edit</a>
            <form method="POST" action="/admin/chatbot/<?= (int)$r['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Delete this response?');">
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
<div class="dash-empty-state"><h3>No responses</h3><p>Add chatbot responses to automate replies.</p></div>
<?php endif; ?>
