<div class="dash-header-premium">
  <div>
    <h1>Schedule</h1>
    <p>Manage your availability</p>
  </div>
</div>

<div class="card mb-2">
  <h3>Availability Settings</h3>
  <form method="POST" action="/specialist/schedule" class="">
    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

    <div class="dash-field">
      <div class="">
        <input type="checkbox" name="is_available" value="1" class="" id="is_available" <?= ($details['is_available'] ?? 1) ? 'checked' : '' ?>>
        <label class="" for="is_available">Available for appointments</label>
      </div>
    </div>

    <div class="dash-field">
      <label for="title">Professional Title</label>
      <input type="text" id="title" name="title" class="" value="<?= htmlspecialchars($details['title'] ?? '') ?>">
    </div>

    <div class="dash-field">
      <label for="bio">Bio / Description</label>
      <textarea id="bio" name="bio" class="" rows="4"><?= htmlspecialchars($details['bio'] ?? '') ?></textarea>
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
            <input type="time" name="<?= $key ?>_start" class="" value="<?= $start ?>">
            <span class="time-slot-sep">to</span>
            <input type="time" name="<?= $key ?>_end" class="" value="<?= $end ?>">
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="form-actions">
      <button type="submit" class="dash-btn dash-btn-primary">Save Changes</button>
    </div>
  </form>
</div>

