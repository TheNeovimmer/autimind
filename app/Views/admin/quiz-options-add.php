<div class="dash-header-premium">
  <div>
    <h1>Add Option</h1>
    <p><?= htmlspecialchars($question['question_text']) ?></p>
  </div>
  <a href="/admin/quiz/<?= (int)$question['id'] ?>/options" class="dash-btn dash-btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<div class="card">
  <?php if (!empty($errors)): ?>
    <div class="dash-alert dash-alert--danger">
      <?php foreach ($errors as $field => $msgs): ?>
        <?php foreach ($msgs as $msg): ?>
          <p><?= htmlspecialchars($msg) ?></p>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="POST" class="">
    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
    <input type="hidden" name="question_id" value="<?= (int)$question['id'] ?>">

    <div class="dash-field">
      <label for="option_text">Option Text</label>
      <input type="text" id="option_text" name="option_text" required>
    </div>

    <div class="dash-field">
      <label for="weight">Weight (0-5)</label>
      <input type="number" id="weight" name="weight" min="0" max="5" value="0" required>
    </div>

    <div class="dash-field">
      <label for="order_index">Order Index</label>
      <input type="number" id="order_index" name="order_index" value="0" required>
    </div>

    <button type="submit" class="dash-btn dash-btn-primary">Create Option</button>
  </form>
</div>
