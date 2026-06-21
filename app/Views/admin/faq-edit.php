<div class="dash-header">
  <div>
    <h1>Edit FAQ</h1>
    <p>Update frequently asked question</p>
  </div>
  <a href="/admin/faq" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<div class="card">
  <form method="POST" class="">
    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

    <div class="mb-3">
      <label for="question" class="form-label">Question</label>
      <input type="text" id="question" name="question" value="<?= htmlspecialchars($faq['question']) ?>" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="answer" class="form-label">Answer</label>
      <textarea id="answer" name="answer" rows="5" class="form-control" required><?= htmlspecialchars($faq['answer']) ?></textarea>
    </div>

    <div class="mb-3">
      <label for="category" class="form-label">Category</label>
      <select id="category" name="category" class="form-select" required>
        <option value="general" <?= $faq['category'] === 'general' ? 'selected' : '' ?>>General</option>
        <option value="features" <?= $faq['category'] === 'features' ? 'selected' : '' ?>>Features</option>
        <option value="pricing" <?= $faq['category'] === 'pricing' ? 'selected' : '' ?>>Pricing</option>
        <option value="technical" <?= $faq['category'] === 'technical' ? 'selected' : '' ?>>Technical</option>
      </select>
    </div>

    <div class="mb-3">
      <label for="order_index" class="form-label">Order</label>
      <input type="number" id="order_index" name="order_index" value="<?= (int)$faq['order_index'] ?>" class="form-control">
    </div>

    <div class="mb-3">
      <div class="form-check">
        <input type="checkbox" name="is_active" value="1" id="is_active" class="form-check-input" <?= $faq['is_active'] ? 'checked' : '' ?>>
        <label for="is_active" class="form-check-label">Active</label>
      </div>
    </div>

    <button type="submit" class="btn btn-primary">Update FAQ</button>
  </form>
</div>
