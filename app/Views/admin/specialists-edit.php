<div class="dash-header">
  <div>
    <h1>Edit Specialist</h1>
    <p><?= htmlspecialchars($user['name']) ?></p>
  </div>
  <a href="/admin/specialists" class="btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
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
      <label>Name</label>
      <input type="text" value="<?= htmlspecialchars($user['name']) ?>" disabled>
    </div>

    <div class="form-group">
      <label>Email</label>
      <input type="text" value="<?= htmlspecialchars($user['email']) ?>" disabled>
    </div>

    <hr style="margin:1rem 0;border-color:#eee;">

    <div class="form-group">
      <label for="title">Professional Title</label>
      <input type="text" id="title" name="title" value="<?= htmlspecialchars($details['title'] ?? '') ?>">
    </div>

    <div class="form-group">
      <label for="bio">Bio</label>
      <textarea id="bio" name="bio" rows="4"><?= htmlspecialchars($details['bio'] ?? '') ?></textarea>
    </div>

    <div class="form-group">
      <label for="specializations">Specializations (JSON array)</label>
      <textarea id="specializations" name="specializations" rows="3"><?= htmlspecialchars($details['specializations'] ?? '') ?></textarea>
    </div>

    <div class="form-group">
      <label for="years_experience">Years Experience</label>
      <input type="number" id="years_experience" name="years_experience" value="<?= (int)($details['years_experience'] ?? 0) ?>" min="0">
    </div>

    <div class="form-group">
      <label class="checkbox-label">
        <input type="checkbox" name="is_available" value="1" <?= !isset($details['is_available']) || $details['is_available'] ? 'checked' : '' ?>>
        Available for Appointments
      </label>
    </div>

    <div class="form-group">
      <label class="checkbox-label">
        <input type="checkbox" name="is_active" value="1" <?= $user['is_active'] ? 'checked' : '' ?>>
        Account Active
      </label>
    </div>

    <button type="submit" class="btn-primary">Update Specialist</button>
  </form>
</div>
