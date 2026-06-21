<div class="dash-header">
  <div>
    <h1>Edit Activity</h1>
    <p><?= htmlspecialchars($activity['title']) ?></p>
  </div>
  <a href="/admin/activities" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<div class="card">
  <?php if (!empty($errors)): ?>
    <div class="flash-error">
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
      <label for="title">Title</label>
      <input type="text" id="title" name="title" value="<?= htmlspecialchars($activity['title']) ?>" required>
    </div>

    <div class="mb-3">
      <label for="description">Description</label>
      <textarea id="description" name="description" rows="3"><?= htmlspecialchars($activity['description'] ?? '') ?></textarea>
    </div>

    <div class="mb-3">
      <label for="category">Category</label>
      <select id="category" name="category" required>
        <option value="games" <?= $activity['category'] === 'games' ? 'selected' : '' ?>>Games</option>
        <option value="puzzles" <?= $activity['category'] === 'puzzles' ? 'selected' : '' ?>>Puzzles</option>
        <option value="stories" <?= $activity['category'] === 'stories' ? 'selected' : '' ?>>Stories</option>
        <option value="video" <?= $activity['category'] === 'video' ? 'selected' : '' ?>>Video</option>
        <option value="coloring" <?= $activity['category'] === 'coloring' ? 'selected' : '' ?>>Coloring</option>
      </select>
    </div>

    <div class="mb-3">
      <label for="difficulty">Difficulty</label>
      <select id="difficulty" name="difficulty" required>
        <option value="easy" <?= $activity['difficulty'] === 'easy' ? 'selected' : '' ?>>Easy</option>
        <option value="medium" <?= $activity['difficulty'] === 'medium' ? 'selected' : '' ?>>Medium</option>
        <option value="hard" <?= $activity['difficulty'] === 'hard' ? 'selected' : '' ?>>Hard</option>
      </select>
    </div>

    <div class="mb-3">
      <label for="image_url">Image URL</label>
      <input type="text" id="image_url" name="image_url" value="<?= htmlspecialchars($activity['image_url'] ?? '') ?>">
    </div>

    <div class="mb-3">
      <label class="checkbox-label">
        <input type="checkbox" name="is_active" value="1" <?= $activity['is_active'] ? 'checked' : '' ?>>
        Active
      </label>
    </div>

    <button type="submit" class="btn btn-primary">Update Activity</button>
  </form>
</div>
