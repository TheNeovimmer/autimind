<div class="dash-header-premium">
  <div>
    <h1>Chatbot Responses</h1>
    <p>Manage automated chatbot responses</p>
  </div>
  <a href="/admin/chatbot/add" class="dash-btn dash-btn-primary"><i class="fas fa-plus"></i> Add Response</a>
</div>

<div class="card dash-field" style="margin-bottom: 24px;">
  <h3><i class="fas fa-robot"></i> OpenRouter Configuration</h3>
  <p style="color: var(--text-muted); margin-bottom: 16px;">The chatbot uses OpenRouter AI for intelligent responses. Configure the model below.</p>
  <div style="display: flex; gap: 24px; align-items: center; flex-wrap: wrap; margin-bottom: 16px;">
    <div>
      <strong>API Key:</strong>
      <?php if ($apiKeySet): ?>
        <span style="color: #10b981;"><i class="fas fa-check-circle"></i> Configured</span>
      <?php else: ?>
        <span style="color: #ef4444;"><i class="fas fa-exclamation-circle"></i> Not set (add OPENROUTER_API_KEY to .env)</span>
      <?php endif; ?>
    </div>
    <div>
      <strong>Current Model:</strong>
      <code style="background: rgba(108,0,144,0.1); padding: 4px 10px; border-radius: 6px; color: #6c0090;"><?= htmlspecialchars($openrouterModel) ?></code>
    </div>
  </div>
  <form method="POST" action="/admin/chatbot/config" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
    <div>
      <label for="openrouter_model" style="display: block; font-size: 13px; margin-bottom: 4px; color: var(--text-muted);">Change Model</label>
      <input type="text" id="openrouter_model" name="openrouter_model" value="<?= htmlspecialchars($openrouterModel) ?>" style="padding: 8px 12px; border: 1px solid var(--border); border-radius: 8px; min-width: 280px; font-family: monospace;">
    </div>
    <button type="submit" class="dash-btn dash-btn-primary">Update Model</button>
  </form>
</div>

<?php if (!empty($responses)): ?>
<div class="dash-table-wrapper">
  <table class="dash-table">
    <thead>
      <tr><th>Keywords</th><th>Response</th><th>Category</th><th>Active</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach ($responses as $r): ?>
        <tr>
          <td><?= htmlspecialchars($r['keywords']) ?></td>
          <td><?= htmlspecialchars(substr($r['response_text'], 0, 80)) ?><?= strlen($r['response_text']) > 80 ? '...' : '' ?></td>
          <td><?= htmlspecialchars($r['category'] ?? '-') ?></td>
          <td><?= $r['is_active'] ? '<span class="status-badge status-badge-active">Yes</span>' : '<span class="status-badge status-badge-cancelled">No</span>' ?></td>
          <td>
            <a href="/admin/chatbot/<?= (int)$r['id'] ?>/edit" class="dash-btn dash-btn-sm dash-btn-outline">Edit</a>
            <form method="POST" action="/admin/chatbot/<?= (int)$r['id'] ?>/delete" class="d-inline-flex" onsubmit="return confirm('Delete this response?');">
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
