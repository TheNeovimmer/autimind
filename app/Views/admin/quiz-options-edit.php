<div class="dash-header">
  <div>
    <h1>Edit Option</h1>
    <p><?= htmlspecialchars($question['question_text'] ?? '') ?></p>
  </div>
  <a href="/admin/quiz/<?= (int)($option['question_id'] ?? 0) ?>/options" class="btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<div class="dash-card">
  <?php if (!empty($errors)): ?>
    <div class="flash-error">
      <?php foreach ($errors as $field => $msgs): ?>
        <?php foreach ($msgs as $msg): ?>
          <p><?= htmlspecialchars($msg) ?></p>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="POST" class="dash-form">
    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

    <div class="form-group">
      <label for="option_text">Option Text</label>
      <input type="text" id="option_text" name="option_text" value="<?= htmlspecialchars($option['option_text']) ?>" required>
    </div>

    <div class="form-group">
      <label for="weight">Weight (0-5)</label>
      <input type="number" id="weight" name="weight" min="0" max="5" value="<?= (int)$option['weight'] ?>" required>
    </div>

    <div class="form-group">
      <label for="order_index">Order Index</label>
      <input type="number" id="order_index" name="order_index" value="<?= (int)$option['order_index'] ?>" required>
    </div>

    <button type="submit" class="btn-primary">Update Option</button>
  </form>
</div>
