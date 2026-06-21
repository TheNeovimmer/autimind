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
      <label>
        <input type="checkbox" name="is_available" value="1" <?= ($details['is_available'] ?? 1) ? 'checked' : '' ?>>
        Available for appointments
      </label>
    </div>

    <div class="mb-3">
      <label for="title">Professional Title</label>
      <input type="text" id="title" name="title" value="<?= htmlspecialchars($details['title'] ?? '') ?>">
    </div>

    <div class="mb-3">
      <label for="bio">Bio / Description</label>
      <textarea id="bio" name="bio" rows="4"><?= htmlspecialchars($details['bio'] ?? '') ?></textarea>
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
            <input type="time" name="<?= $key ?>_start" value="<?= $start ?>">
            <span class="time-slot-sep">to</span>
            <input type="time" name="<?= $key ?>_end" value="<?= $end ?>">
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="d-flex gap-2 align-items-center mt-3">
      <button type="submit" class="btn btn-primary">Save Changes</button>
    </div>
  </form>
</div>

<style>
.time-slots-grid { display:grid; gap:0.75rem; }
.time-slot-day { display:flex; align-items:center; gap:1rem; padding:0.5rem 0; border-bottom:1px solid var(--border,#e5e7eb); }
.time-slot-label { min-width:100px; font-weight:600; font-size:0.9rem; }
.time-slot-inputs { display:flex; align-items:center; gap:0.5rem; }
.time-slot-inputs input[type="time"] { padding:0.4rem 0.6rem; border:1px solid var(--border,#d1d5db); border-radius:6px; font-size:0.9rem; background:var(--input-bg,#fff); }
.time-slot-sep { font-size:0.85rem; color:#6b7280; }
</style>
