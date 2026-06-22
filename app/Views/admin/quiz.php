<div class="dash-header-premium">
  <div>
    <h1>Quiz Questions</h1>
    <p>Manage screening quiz questions and options</p>
  </div>
  <a href="/admin/quiz/add" class="dash-btn dash-btn-primary"><i class="fas fa-plus"></i> Add Question</a>
</div>

<?php if (!empty($questions)): ?>
<div class="dash-table-wrapper">
  <table class="dash-table">
    <thead>
      <tr><th>Order</th><th>Question</th><th>Category</th><th>Options</th><th>Active</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach ($questions as $q): ?>
        <tr>
          <td><?= (int)$q['order_index'] ?></td>
          <td><?= htmlspecialchars(substr($q['question_text'], 0, 80)) ?></td>
          <td><span class="status-badge status-badge-completed"><?= str_replace('_', ' ', htmlspecialchars($q['category'])) ?></span></td>
          <td><?= (int)$q['option_count'] ?></td>
          <td><?= $q['is_active'] ? '<span class="status-badge status-badge-active">Yes</span>' : '<span class="status-badge status-badge-cancelled">No</span>' ?></td>
          <td>
            <a href="/admin/quiz/<?= (int)$q['id'] ?>/options" class="dash-btn dash-btn-sm dash-btn-outline">Options</a>
            <a href="/admin/quiz/<?= (int)$q['id'] ?>/edit" class="dash-btn dash-btn-sm dash-btn-outline">Edit</a>
            <form method="POST" action="/admin/quiz/<?= (int)$q['id'] ?>/delete" class="d-inline-flex" onsubmit="return confirm('Delete this question?');">
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
<div class="dash-empty-state"><h3>No questions</h3><p>Add quiz questions to enable screenings.</p></div>
<?php endif; ?>
