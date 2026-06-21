<div class="dash-header">
  <div>
    <h1>Edit Chatbot Response</h1>
    <p>Update automated chatbot response</p>
  </div>
  <a href="/admin/chatbot" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
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
      <label for="keywords" class="form-label">Keywords (comma-separated)</label>
      <input type="text" id="keywords" name="keywords" value="<?= htmlspecialchars($response['keywords']) ?>" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="response_text" class="form-label">Response Text</label>
      <textarea id="response_text" name="response_text" rows="5" class="form-control" required><?= htmlspecialchars($response['response_text']) ?></textarea>
    </div>

    <div class="mb-3">
      <label for="category" class="form-label">Category</label>
      <input type="text" id="category" name="category" value="<?= htmlspecialchars($response['category'] ?? '') ?>" class="form-control">
    </div>

    <div class="mb-3">
      <div class="form-check">
        <input type="checkbox" name="is_active" value="1" id="is_active" class="form-check-input" <?= $response['is_active'] ? 'checked' : '' ?>>
        <label for="is_active" class="form-check-label">Active</label>
      </div>
    </div>

    <button type="submit" class="btn btn-primary">Update Response</button>
  </form>
</div>
