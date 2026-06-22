<div class="dash-header-premium">
  <div>
    <h1>FAQ Items</h1>
    <p>Manage frequently asked questions</p>
  </div>
  <a href="/admin/faq/add" class="dash-btn dash-btn-primary"><i class="fas fa-plus"></i> Add FAQ</a>
</div>

<?php if (!empty($faqs)): ?>
<div class="dash-table-wrapper">
  <table class="dash-table">
    <thead>
      <tr><th>Order</th><th>Question</th><th>Category</th><th>Active</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach ($faqs as $f): ?>
        <tr>
          <td><?= (int)$f['order_index'] ?></td>
          <td><?= htmlspecialchars(substr($f['question'], 0, 80)) ?></td>
          <td><span class="status-badge status-badge-completed"><?= htmlspecialchars($f['category']) ?></span></td>
          <td><?= $f['is_active'] ? '<span class="status-badge status-badge-active">Yes</span>' : '<span class="status-badge status-badge-cancelled">No</span>' ?></td>
          <td>
            <a href="/admin/faq/<?= (int)$f['id'] ?>/edit" class="dash-btn dash-btn-sm dash-btn-outline">Edit</a>
            <form method="POST" action="/admin/faq/<?= (int)$f['id'] ?>/delete" class="d-inline-flex" onsubmit="return confirm('Delete this FAQ?');">
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
<div class="dash-empty-state"><h3>No FAQ items</h3><p>Add FAQ items to help users find answers.</p></div>
<?php endif; ?>
