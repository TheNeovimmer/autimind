<div class="dash-header-premium">
  <div>
    <h1>Edit FAQ</h1>
    <p>Update frequently asked question</p>
  </div>
  <a href="/admin/faq" class="dash-btn dash-btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<div class="card">
  <form method="POST" class="">
    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

    <div class="dash-field">
      <label for="question">Question</label>
      <input type="text" id="question" name="question" value="<?= htmlspecialchars($faq['question']) ?>" required>
    </div>

    <div class="dash-field">
      <label for="answer">Answer</label>
      <textarea id="answer" name="answer" rows="5" required><?= htmlspecialchars($faq['answer']) ?></textarea>
    </div>

    <div class="dash-field">
      <label for="category">Category</label>
      <select id="category" name="category" required>
        <option value="general" <?= $faq['category'] === 'general' ? 'selected' : '' ?>>General</option>
        <option value="features" <?= $faq['category'] === 'features' ? 'selected' : '' ?>>Features</option>
        <option value="pricing" <?= $faq['category'] === 'pricing' ? 'selected' : '' ?>>Pricing</option>
        <option value="technical" <?= $faq['category'] === 'technical' ? 'selected' : '' ?>>Technical</option>
      </select>
    </div>

    <div class="dash-field">
      <label for="order_index">Order</label>
      <input type="number" id="order_index" name="order_index" value="<?= (int)$faq['order_index'] ?>">
    </div>

    <div class="dash-field">
      <div>
        <input type="checkbox" name="is_active" value="1" id="is_active" <?= $faq['is_active'] ? 'checked' : '' ?>>
        <label for="is_active">Active</label>
      </div>
    </div>

    <button type="submit" class="dash-btn dash-btn-primary">Update FAQ</button>
  </form>
</div>
