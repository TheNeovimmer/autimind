<div class="dash-header">
  <div>
    <h1>Edit Chatbot Response</h1>
    <p>Update automated chatbot response</p>
  </div>
  <a href="/admin/chatbot" class="btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
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
      <label for="keywords">Keywords (comma-separated)</label>
      <input type="text" id="keywords" name="keywords" value="<?= htmlspecialchars($response['keywords']) ?>" required>
    </div>

    <div class="form-group">
      <label for="response_text">Response Text</label>
      <textarea id="response_text" name="response_text" rows="5" required><?= htmlspecialchars($response['response_text']) ?></textarea>
    </div>

    <div class="form-group">
      <label for="category">Category</label>
      <input type="text" id="category" name="category" value="<?= htmlspecialchars($response['category'] ?? '') ?>">
    </div>

    <div class="form-group">
      <label class="checkbox-label">
        <input type="checkbox" name="is_active" value="1" <?= $response['is_active'] ? 'checked' : '' ?>>
        Active
      </label>
    </div>

    <button type="submit" class="btn-primary">Update Response</button>
  </form>
</div>
