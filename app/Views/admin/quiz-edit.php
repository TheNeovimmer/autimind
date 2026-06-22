<div class="dash-header-premium">
  <div>
    <h1>Edit Question</h1>
    <p>Update screening question and options</p>
  </div>
  <a href="/admin/quiz" class="dash-btn dash-btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
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

    <div class="dash-field">
      <label for="question_text">Question Text</label>
      <textarea id="question_text" name="question_text" rows="3" required><?= htmlspecialchars($question['question_text']) ?></textarea>
    </div>

    <div class="dash-field">
      <label for="category">Category</label>
      <select id="category" name="category" required>
        <option value="social_communication" <?= $question['category'] === 'social_communication' ? 'selected' : '' ?>>Social Communication</option>
        <option value="behavior" <?= $question['category'] === 'behavior' ? 'selected' : '' ?>>Behavior</option>
        <option value="sensory" <?= $question['category'] === 'sensory' ? 'selected' : '' ?>>Sensory</option>
        <option value="developmental" <?= $question['category'] === 'developmental' ? 'selected' : '' ?>>Developmental</option>
      </select>
    </div>

    <div class="dash-field">
      <label for="order_index">Order</label>
      <input type="number" id="order_index" name="order_index" value="<?= (int)$question['order_index'] ?>" required>
    </div>

    <div class="dash-field">
      <div>
        <input type="checkbox" name="is_active" value="1" id="is_active" <?= $question['is_active'] ? 'checked' : '' ?>>
        <label for="is_active">Active</label>
      </div>
    </div>

    <div class="dash-field">
      <label>Options</label>
      <div id="optionsContainer">
        <?php if (!empty($options)): ?>
          <?php foreach ($options as $i => $opt): ?>
            <div class="option-row form-grid-3">
              <input type="text" name="options[<?= $i ?>][text]" value="<?= htmlspecialchars($opt['option_text']) ?>" required>
              <input type="number" name="options[<?= $i ?>][weight]" value="<?= (int)$opt['weight'] ?>" min="0" max="5" required>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="option-row form-grid-3">
            <input type="text" name="options[0][text]" placeholder="Option text" required>
            <input type="number" name="options[0][weight]" placeholder="Weight" min="0" max="5" value="0" required>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <button type="submit" class="dash-btn dash-btn-primary">Update Question</button>
  </form>
</div>
