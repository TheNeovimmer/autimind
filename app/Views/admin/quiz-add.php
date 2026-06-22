<div class="dash-header-premium">
  <div>
    <h1>Add Question</h1>
    <p>Create a new screening question</p>
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

  <form method="POST" class="" id="quizForm">
    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

    <div class="dash-field">
      <label for="question_text">Question Text</label>
      <textarea id="question_text" name="question_text" rows="3" required></textarea>
    </div>

    <div class="dash-field">
      <label for="category">Category</label>
      <select id="category" name="category" required>
        <option value="social_communication">Social Communication</option>
        <option value="behavior">Behavior</option>
        <option value="sensory">Sensory</option>
        <option value="developmental">Developmental</option>
      </select>
    </div>

    <div class="dash-field">
      <label for="order_index">Order</label>
      <input type="number" id="order_index" name="order_index" value="0" required>
    </div>

    <div class="dash-field">
      <label>Options (at least 2 recommended)</label>
      <div id="optionsContainer">
        <div class="option-row form-grid-3">
          <input type="text" name="options[0][text]" placeholder="Option text" required>
          <input type="number" name="options[0][weight]" placeholder="Weight (0-5)" min="0" max="5" value="0" required>
        </div>
        <div class="option-row form-grid-3">
          <input type="text" name="options[1][text]" placeholder="Option text" required>
          <input type="number" name="options[1][weight]" placeholder="Weight (0-5)" min="0" max="5" value="1" required>
        </div>
        <div class="option-row form-grid-3">
          <input type="text" name="options[2][text]" placeholder="Option text">
          <input type="number" name="options[2][weight]" placeholder="Weight (0-5)" min="0" max="5" value="2">
        </div>
        <div class="option-row form-grid-3">
          <input type="text" name="options[3][text]" placeholder="Option text">
          <input type="number" name="options[3][weight]" placeholder="Weight (0-5)" min="0" max="5" value="3">
        </div>
      </div>
      <button type="button" class="dash-btn dash-btn-sm dash-btn-outline" onclick="addOption()">+ Add Option</button>
    </div>

    <button type="submit" class="dash-btn dash-btn-primary">Create Question</button>
  </form>
</div>

<script>
let optionIndex = 4;
function addOption() {
  const container = document.getElementById('optionsContainer');
  const div = document.createElement('div');
  div.className = 'option-row form-grid-3';
  div.innerHTML = '<input type="text" name="options[' + optionIndex + '][text]" placeholder="Option text">' +
    '<input type="number" name="options[' + optionIndex + '][weight]" placeholder="Weight (0-5)" min="0" max="5" value="0">';
  container.appendChild(div);
  optionIndex++;
}
</script>
