<div class="dash-header">
  <div>
    <h1>Add Activity</h1>
    <p>Create a new children's activity</p>
  </div>
  <a href="/admin/activities" class="btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
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
      <label for="title">Title</label>
      <input type="text" id="title" name="title" required maxlength="255">
    </div>

    <div class="form-group">
      <label for="description">Description</label>
      <textarea id="description" name="description" rows="3"></textarea>
    </div>

    <div class="form-group">
      <label for="category">Category</label>
      <select id="category" name="category" required>
        <option value="games">Games</option>
        <option value="puzzles">Puzzles</option>
        <option value="stories">Stories</option>
        <option value="video">Video</option>
        <option value="coloring">Coloring</option>
      </select>
    </div>

    <div class="form-group">
      <label for="difficulty">Difficulty</label>
      <select id="difficulty" name="difficulty" required>
        <option value="easy">Easy</option>
        <option value="medium">Medium</option>
        <option value="hard">Hard</option>
      </select>
    </div>

    <div class="form-group">
      <label for="image_url">Image URL</label>
      <input type="text" id="image_url" name="image_url" placeholder="https://example.com/image.jpg">
    </div>

    <button type="submit" class="btn-primary">Create Activity</button>
  </form>
</div>
