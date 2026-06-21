<div class="dash-header">
  <div>
    <h1>Book Appointment</h1>
    <p>Schedule a session with a specialist</p>
  </div>
</div>

<form method="POST" action="/parent/appointments/book" >
  <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
  
  <div class="mb-3">
    <label for="child_id" class="form-label">Child *</label>
    <select id="child_id" name="child_id" class="form-select" required>
      <option value="">Select a child</option>
      <?php foreach ($children as $child): ?>
        <option value="<?= (int)$child['id'] ?>"><?= htmlspecialchars($child['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="mb-3">
    <label for="specialist_id" class="form-label">Specialist *</label>
    <select id="specialist_id" name="specialist_id" class="form-select" required>
      <option value="">Select a specialist</option>
      <?php foreach ($specialists as $spec): ?>
        <option value="<?= (int)$spec['id'] ?>"><?= htmlspecialchars($spec['name']) ?> — <?= htmlspecialchars($spec['title'] ?? '') ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="row row-cols-1 row-cols-md-2 g-3">
    <div class="mb-3">
      <label for="date" class="form-label">Date *</label>
      <input type="date" id="date" name="date" class="form-control" required min="<?= date('Y-m-d') ?>">
    </div>
    <div class="mb-3">
      <label for="time" class="form-label">Time *</label>
      <input type="time" id="time" name="time" class="form-control" required>
    </div>
    <div class="mb-3">
      <label for="duration" class="form-label">Duration (minutes)</label>
      <select id="duration" name="duration" class="form-select">
        <option value="30">30 min</option>
        <option value="45">45 min</option>
        <option value="60">60 min</option>
      </select>
    </div>
  </div>

  <div class="mb-3">
    <label for="notes" class="form-label">Notes</label>
    <textarea id="notes" name="notes" rows="3" class="form-control" placeholder="Any specific concerns or topics you'd like to discuss..."><?= htmlspecialchars($old['notes'] ?? '') ?></textarea>
  </div>

  <div class="d-flex gap-2 align-items-center">
    <a href="/parent/appointments" class="dash-btn dash-btn-outline">Cancel</a>
    <button type="submit" class="dash-btn dash-btn-primary">Book Appointment</button>
  </div>
</form>
