<div class="dash-header-premium">
  <div>
    <h1>Edit Chatbot Response</h1>
    <p>Update automated chatbot response</p>
  </div>
  <a href="/admin/chatbot" class="dash-btn dash-btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
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
      <label for="keywords">Keywords (comma-separated)</label>
      <input type="text" id="keywords" name="keywords" value="<?= htmlspecialchars($response['keywords']) ?>" required>
    </div>

    <div class="dash-field">
      <label for="response_text">Response Text</label>
      <textarea id="response_text" name="response_text" rows="5" required><?= htmlspecialchars($response['response_text']) ?></textarea>
    </div>

    <div class="dash-field">
      <label for="category">Category</label>
      <input type="text" id="category" name="category" value="<?= htmlspecialchars($response['category'] ?? '') ?>">
    </div>

    <div class="dash-field">
      <div>
        <input type="checkbox" name="is_active" value="1" id="is_active" <?= $response['is_active'] ? 'checked' : '' ?>>
        <label for="is_active">Active</label>
      </div>
    </div>

    <button type="submit" class="dash-btn dash-btn-primary">Update Response</button>
  </form>
</div>
