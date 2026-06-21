<div class="dash-header">
  <div>
    <h1>Schedule</h1>
    <p>Manage your availability</p>
  </div>
</div>

<div class="card mb-2">
  <h3>Availability Settings</h3>
  <form method="POST" action="/specialist/schedule" class="">
    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

    <div class="mb-3">
      <div class="form-check">
        <input type="checkbox" name="is_available" value="1" class="form-check-input" id="is_available" <?= ($details['is_available'] ?? 1) ? 'checked' : '' ?>>
        <label class="form-check-label" for="is_available">Available for appointments</label>
      </div>
    </div>

    <div class="mb-3">
      <label for="title" class="form-label">Professional Title</label>
      <input type="text" id="title" name="title" class="form-control" value="<?= htmlspecialchars($details['title'] ?? '') ?>">
    </div>

    <div class="mb-3">
      <label for="bio" class="form-label">Bio / Description</label>
      <textarea id="bio" name="bio" class="form-control" rows="4"><?= htmlspecialchars($details['bio'] ?? '') ?></textarea>
    </div>

    <h4 class="mt-3 mb-1">Weekly Time Slots</h4>
    <div class="time-slots-grid">
      <?php
      $days = ['monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday', 'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday', 'sunday' => 'Sunday'];
      foreach ($days as $key => $label):
        $start = htmlspecialchars($availability[$key]['start'] ?? '09:00');
        $end = htmlspecialchars($availability[$key]['end'] ?? '17:00');
      ?>
        <div class="time-slot-day">
          <label class="time-slot-label"><?= $label ?></label>
          <div class="time-slot-inputs">
            <input type="time" name="<?= $key ?>_start" class="form-control" value="<?= $start ?>">
            <span class="time-slot-sep">to</span>
            <input type="time" name="<?= $key ?>_end" class="form-control" value="<?= $end ?>">
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="d-flex gap-2 align-items-center mt-3">
      <button type="submit" class="dash-btn dash-btn-primary">Save Changes</button>
    </div>
  </form>
</div>

