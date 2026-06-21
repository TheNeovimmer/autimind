<div class="dash-header">
  <div>
    <h1>Edit Specialist</h1>
    <p><?= htmlspecialchars($user['name']) ?></p>
  </div>
  <a href="/admin/specialists" class="dash-btn dash-btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
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
      <label class="form-label">Name</label>
      <input type="text" value="<?= htmlspecialchars($user['name']) ?>" disabled class="form-control">
    </div>

    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="text" value="<?= htmlspecialchars($user['email']) ?>" disabled class="form-control">
    </div>

    <hr class="dash-divider">

    <div class="mb-3">
      <label for="title" class="form-label">Professional Title</label>
      <input type="text" id="title" name="title" value="<?= htmlspecialchars($details['title'] ?? '') ?>" class="form-control">
    </div>

    <div class="mb-3">
      <label for="bio" class="form-label">Bio</label>
      <textarea id="bio" name="bio" rows="4" class="form-control"><?= htmlspecialchars($details['bio'] ?? '') ?></textarea>
    </div>

    <div class="mb-3">
      <label for="specializations" class="form-label">Specializations (JSON array)</label>
      <textarea id="specializations" name="specializations" rows="3" class="form-control"><?= htmlspecialchars($details['specializations'] ?? '') ?></textarea>
    </div>

    <div class="mb-3">
      <label for="years_experience" class="form-label">Years Experience</label>
      <input type="number" id="years_experience" name="years_experience" value="<?= (int)($details['years_experience'] ?? 0) ?>" min="0" class="form-control">
    </div>

    <div class="mb-3">
      <div class="form-check">
        <input type="checkbox" name="is_available" value="1" id="is_available" class="form-check-input" <?= !isset($details['is_available']) || $details['is_available'] ? 'checked' : '' ?>>
        <label for="is_available" class="form-check-label">Available for Appointments</label>
      </div>
    </div>

    <div class="mb-3">
      <div class="form-check">
        <input type="checkbox" name="is_active" value="1" id="is_active_sp" class="form-check-input" <?= $user['is_active'] ? 'checked' : '' ?>>
        <label for="is_active_sp" class="form-check-label">Account Active</label>
      </div>
    </div>

    <button type="submit" class="dash-btn dash-btn-primary">Update Specialist</button>
  </form>
</div>
