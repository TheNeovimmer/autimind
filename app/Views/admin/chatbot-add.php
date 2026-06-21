<div class="dash-header">
  <div>
    <h1>Add Chatbot Response</h1>
    <p>Create a new automated chatbot response</p>
  </div>
  <a href="/admin/chatbot" class="dash-btn dash-btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
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
      <input type="text" id="keywords" name="keywords" placeholder="hello, hi, greeting" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="response_text" class="form-label">Response Text</label>
      <textarea id="response_text" name="response_text" rows="5" class="form-control" required></textarea>
    </div>

    <div class="mb-3">
      <label for="category" class="form-label">Category</label>
      <input type="text" id="category" name="category" placeholder="e.g. greeting, support, faq" class="form-control">
    </div>

    <button type="submit" class="dash-btn dash-btn-primary">Create Response</button>
  </form>
</div>
