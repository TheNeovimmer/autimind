<div class="dash-header-premium">
  <div>
    <h1>Add FAQ</h1>
    <p>Create a new frequently asked question</p>
  </div>
  <a href="/admin/faq" class="dash-btn dash-btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
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
      <label for="question">Question</label>
      <input type="text" id="question" name="question" required>
    </div>

    <div class="dash-field">
      <label for="answer">Answer</label>
      <textarea id="answer" name="answer" rows="5" required></textarea>
    </div>

    <div class="dash-field">
      <label for="category">Category</label>
      <select id="category" name="category" required>
        <option value="general">General</option>
        <option value="features">Features</option>
        <option value="pricing">Pricing</option>
        <option value="technical">Technical</option>
      </select>
    </div>

    <div class="dash-field">
      <label for="order_index">Order</label>
      <input type="number" id="order_index" name="order_index" value="0">
    </div>

    <button type="submit" class="dash-btn dash-btn-primary">Create FAQ</button>
  </form>
</div>
