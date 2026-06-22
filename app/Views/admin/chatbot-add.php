<div class="dash-header-premium">
  <div>
    <h1>Add Chatbot Response</h1>
    <p>Create a new automated chatbot response</p>
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
      <input type="text" id="keywords" name="keywords" placeholder="hello, hi, greeting" required>
    </div>

    <div class="dash-field">
      <label for="response_text">Response Text</label>
      <textarea id="response_text" name="response_text" rows="5" required></textarea>
    </div>

    <div class="dash-field">
      <label for="category">Category</label>
      <input type="text" id="category" name="category" placeholder="e.g. greeting, support, faq">
    </div>

    <button type="submit" class="dash-btn dash-btn-primary">Create Response</button>
  </form>
</div>
