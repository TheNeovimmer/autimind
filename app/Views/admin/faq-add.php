<div class="dash-header">
  <div>
    <h1>Add FAQ</h1>
    <p>Create a new frequently asked question</p>
  </div>
  <a href="/admin/faq" class="btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
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
      <label for="question">Question</label>
      <input type="text" id="question" name="question" required>
    </div>

    <div class="form-group">
      <label for="answer">Answer</label>
      <textarea id="answer" name="answer" rows="5" required></textarea>
    </div>

    <div class="form-group">
      <label for="category">Category</label>
      <select id="category" name="category" required>
        <option value="general">General</option>
        <option value="features">Features</option>
        <option value="pricing">Pricing</option>
        <option value="technical">Technical</option>
      </select>
    </div>

    <div class="form-group">
      <label for="order_index">Order</label>
      <input type="number" id="order_index" name="order_index" value="0">
    </div>

    <button type="submit" class="btn-primary">Create FAQ</button>
  </form>
</div>
