<div class="dash-header">
  <div>
    <h1>Quiz Options</h1>
    <p><?= htmlspecialchars($question['question_text']) ?></p>
  </div>
  <div>
    <a href="/admin/quiz/options/add/<?= (int)$question['id'] ?>" class="btn-primary"><i class="fas fa-plus"></i> Add Option</a>
    <a href="/admin/quiz" class="btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
  </div>
</div>

<?php if (!empty($options)): ?>
<div class="table-responsive">
  <table class="dash-table">
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
            <a href="/admin/quiz/options/<?= (int)$o['id'] ?>/edit" class="btn-sm btn-outline">Edit</a>
            <form method="POST" action="/admin/quiz/options/<?= (int)$o['id'] ?>/delete" style="display:inline;" onsubmit="return confirm('Delete this option?');">
              <input type="hidden" name="_csrf_token" value="<?= \App\Core\Session::csrf_token() ?>">
              <button type="submit" class="btn-sm" style="background:#fee2e2;color:#dc2626;border:none;cursor:pointer;">Delete</button>
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
