<div class="dash-header">
  <div>
    <h1>Edit Activity</h1>
    <p><?= htmlspecialchars($activity['title']) ?></p>
  </div>
  <a href="/admin/activities" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
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
      <label for="title" class="form-label">Title</label>
      <input type="text" id="title" name="title" value="<?= htmlspecialchars($activity['title']) ?>" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="description" class="form-label">Description</label>
      <textarea id="description" name="description" rows="3" class="form-control"><?= htmlspecialchars($activity['description'] ?? '') ?></textarea>
    </div>

    <div class="mb-3">
      <label for="category" class="form-label">Category</label>
      <select id="category" name="category" class="form-select" required>
        <option value="games" <?= $activity['category'] === 'games' ? 'selected' : '' ?>>Games</option>
        <option value="puzzles" <?= $activity['category'] === 'puzzles' ? 'selected' : '' ?>>Puzzles</option>
        <option value="stories" <?= $activity['category'] === 'stories' ? 'selected' : '' ?>>Stories</option>
        <option value="video" <?= $activity['category'] === 'video' ? 'selected' : '' ?>>Video</option>
        <option value="coloring" <?= $activity['category'] === 'coloring' ? 'selected' : '' ?>>Coloring</option>
      </select>
    </div>

    <div class="mb-3">
      <label for="difficulty" class="form-label">Difficulty</label>
      <select id="difficulty" name="difficulty" class="form-select" required>
        <option value="easy" <?= $activity['difficulty'] === 'easy' ? 'selected' : '' ?>>Easy</option>
        <option value="medium" <?= $activity['difficulty'] === 'medium' ? 'selected' : '' ?>>Medium</option>
        <option value="hard" <?= $activity['difficulty'] === 'hard' ? 'selected' : '' ?>>Hard</option>
      </select>
    </div>

    <div class="mb-3">
      <label for="image_url" class="form-label">Image URL</label>
      <input type="text" id="image_url" name="image_url" value="<?= htmlspecialchars($activity['image_url'] ?? '') ?>" class="form-control">
    </div>

    <div class="mb-3">
      <div class="form-check">
        <input type="checkbox" name="is_active" value="1" id="is_active" class="form-check-input" <?= $activity['is_active'] ? 'checked' : '' ?>>
        <label for="is_active" class="form-check-label">Active</label>
      </div>
    </div>

    <button type="submit" class="btn btn-primary">Update Activity</button>
  </form>
</div>
