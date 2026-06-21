<div class="dash-header">
  <div>
    <h1>FAQ Items</h1>
    <p>Manage frequently asked questions</p>
  </div>
  <a href="/admin/faq/add" class="dash-btn dash-btn-primary"><i class="fas fa-plus"></i> Add FAQ</a>
</div>

<?php if (!empty($faqs)): ?>
<div class="table-responsive">
  <table class="table table-hover align-middle mb-0 small">
    <thead>
      <tr><th>Order</th><th>Question</th><th>Category</th><th>Active</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach ($faqs as $f): ?>
        <tr>
          <td><?= (int)$f['order_index'] ?></td>
          <td><?= htmlspecialchars(substr($f['question'], 0, 80)) ?></td>
          <td><span class="badge bg-primary-subtle text-primary-emphasis"><?= htmlspecialchars($f['category']) ?></span></td>
          <td><?= $f['is_active'] ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>' ?></td>
          <td>
            <a href="/admin/faq/<?= (int)$f['id'] ?>/edit" class="dash-btn dash-btn-sm dash-btn-outline">Edit</a>
            <form method="POST" action="/admin/faq/<?= (int)$f['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Delete this FAQ?');">
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
