<div class="dash-header">
  <div>
    <h1>Quiz Options</h1>
    <p><?= htmlspecialchars($question['question_text']) ?></p>
  </div>
  <div>
    <a href="/admin/quiz/options/add/<?= (int)$question['id'] ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Add Option</a>
    <a href="/admin/quiz" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
  </div>
</div>

<?php if (!empty($options)): ?>
<div class="table-responsive">
  <table class="table table-hover align-middle mb-0 small">
    <thead>
      <tr><th>Order</th><th>Option Text</th><th>Weight</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach ($options as $o): ?>
        <tr>
          <td><?= (int)$o['order_index'] ?></td>
          <td><?= htmlspecialchars($o['option_text']) ?></td>
          <td><?= (int)$o['weight'] ?></td>
          <td>
            <a href="/admin/quiz/options/<?= (int)$o['id'] ?>/edit" class="btn btn-sm btn-outline-secondary">Edit</a>
            <form method="POST" action="/admin/quiz/options/<?= (int)$o['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Delete this option?');">
              <input type="hidden" name="_csrf_token" value="<?= \App\Core\Session::csrf_token() ?>">
              <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
<div class="dash-empty-state"><h3>No options</h3><p>This question has no options yet.</p></div>
<?php endif; ?>
