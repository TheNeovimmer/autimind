<div class="dash-header-premium">
  <div>
    <h1>Edit Specialist</h1>
    <p><?= htmlspecialchars($user['name']) ?></p>
  </div>
  <a href="/admin/specialists" class="dash-btn dash-btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
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
      <label>Name</label>
      <input type="text" value="<?= htmlspecialchars($user['name']) ?>" disabled>
    </div>

    <div class="dash-field">
      <label>Email</label>
      <input type="text" value="<?= htmlspecialchars($user['email']) ?>" disabled>
    </div>

    <hr class="dash-divider">

    <div class="dash-field">
      <label for="title">Professional Title</label>
      <input type="text" id="title" name="title" value="<?= htmlspecialchars($details['title'] ?? '') ?>">
    </div>

    <div class="dash-field">
      <label for="bio">Bio</label>
      <textarea id="bio" name="bio" rows="4"><?= htmlspecialchars($details['bio'] ?? '') ?></textarea>
    </div>

    <div class="dash-field">
      <label for="specializations">Specializations (JSON array)</label>
      <textarea id="specializations" name="specializations" rows="3"><?= htmlspecialchars($details['specializations'] ?? '') ?></textarea>
    </div>

    <div class="dash-field">
      <label for="years_experience">Years Experience</label>
      <input type="number" id="years_experience" name="years_experience" value="<?= (int)($details['years_experience'] ?? 0) ?>" min="0">
    </div>

    <div class="dash-field">
      <div>
        <input type="checkbox" name="is_available" value="1" id="is_available" <?= !isset($details['is_available']) || $details['is_available'] ? 'checked' : '' ?>>
        <label for="is_available">Available for Appointments</label>
      </div>
    </div>

    <div class="dash-field">
      <div>
        <input type="checkbox" name="is_active" value="1" id="is_active_sp" <?= $user['is_active'] ? 'checked' : '' ?>>
        <label for="is_active_sp">Account Active</label>
      </div>
    </div>

    <button type="submit" class="dash-btn dash-btn-primary">Update Specialist</button>
  </form>
</div>
