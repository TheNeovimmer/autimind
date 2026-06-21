<div class="dash-header">
  <div>
    <h1>Add FAQ</h1>
    <p>Create a new frequently asked question</p>
  </div>
  <a href="/admin/faq" class="dash-btn dash-btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
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
      <label for="question" class="form-label">Question</label>
      <input type="text" id="question" name="question" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="answer" class="form-label">Answer</label>
      <textarea id="answer" name="answer" rows="5" class="form-control" required></textarea>
    </div>

    <div class="mb-3">
      <label for="category" class="form-label">Category</label>
      <select id="category" name="category" class="form-select" required>
        <option value="general">General</option>
        <option value="features">Features</option>
        <option value="pricing">Pricing</option>
        <option value="technical">Technical</option>
      </select>
    </div>

    <div class="mb-3">
      <label for="order_index" class="form-label">Order</label>
      <input type="number" id="order_index" name="order_index" value="0" class="form-control">
    </div>

    <button type="submit" class="dash-btn dash-btn-primary">Create FAQ</button>
  </form>
</div>
