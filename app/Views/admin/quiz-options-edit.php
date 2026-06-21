<div class="dash-header">
  <div>
    <h1>Edit Option</h1>
    <p><?= htmlspecialchars($question['question_text'] ?? '') ?></p>
  </div>
  <a href="/admin/quiz/<?= (int)($option['question_id'] ?? 0) ?>/options" class="dash-btn dash-btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<div class="card">
  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <?php foreach ($errors as $field => $msgs): ?>
        <?php foreach ($msgs as $msg): ?>
          <p><?= htmlspecialchars($msg) ?></p>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="POST" class="">
    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

    <div class="mb-3">
      <label for="option_text" class="form-label">Option Text</label>
      <input type="text" id="option_text" name="option_text" value="<?= htmlspecialchars($option['option_text']) ?>" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="weight" class="form-label">Weight (0-5)</label>
      <input type="number" id="weight" name="weight" min="0" max="5" value="<?= (int)$option['weight'] ?>" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="order_index" class="form-label">Order Index</label>
      <input type="number" id="order_index" name="order_index" value="<?= (int)$option['order_index'] ?>" class="form-control" required>
    </div>

    <button type="submit" class="dash-btn dash-btn-primary">Update Option</button>
  </form>
</div>
