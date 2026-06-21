<div class="dash-header">
  <div>
    <h1>Edit Question</h1>
    <p>Update screening question and options</p>
  </div>
  <a href="/admin/quiz" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
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
      <label for="question_text" class="form-label">Question Text</label>
      <textarea id="question_text" name="question_text" rows="3" class="form-control" required><?= htmlspecialchars($question['question_text']) ?></textarea>
    </div>

    <div class="mb-3">
      <label for="category" class="form-label">Category</label>
      <select id="category" name="category" class="form-select" required>
        <option value="social_communication" <?= $question['category'] === 'social_communication' ? 'selected' : '' ?>>Social Communication</option>
        <option value="behavior" <?= $question['category'] === 'behavior' ? 'selected' : '' ?>>Behavior</option>
        <option value="sensory" <?= $question['category'] === 'sensory' ? 'selected' : '' ?>>Sensory</option>
        <option value="developmental" <?= $question['category'] === 'developmental' ? 'selected' : '' ?>>Developmental</option>
      </select>
    </div>

    <div class="mb-3">
      <label for="order_index" class="form-label">Order</label>
      <input type="number" id="order_index" name="order_index" value="<?= (int)$question['order_index'] ?>" class="form-control" required>
    </div>

    <div class="mb-3">
      <div class="form-check">
        <input type="checkbox" name="is_active" value="1" id="is_active" class="form-check-input" <?= $question['is_active'] ? 'checked' : '' ?>>
        <label for="is_active" class="form-check-label">Active</label>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Options</label>
      <div id="optionsContainer">
        <?php if (!empty($options)): ?>
          <?php foreach ($options as $i => $opt): ?>
            <div class="option-row form-grid-3">
              <input type="text" name="options[<?= $i ?>][text]" value="<?= htmlspecialchars($opt['option_text']) ?>" class="form-control" required>
              <input type="number" name="options[<?= $i ?>][weight]" value="<?= (int)$opt['weight'] ?>" min="0" max="5" class="form-control" required>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="option-row form-grid-3">
            <input type="text" name="options[0][text]" placeholder="Option text" class="form-control" required>
            <input type="number" name="options[0][weight]" placeholder="Weight" min="0" max="5" value="0" class="form-control" required>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <button type="submit" class="btn btn-primary">Update Question</button>
  </form>
</div>
