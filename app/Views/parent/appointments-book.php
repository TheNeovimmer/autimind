<div class="dash-header">
  <div>
    <h1>Book Appointment</h1>
    <p>Schedule a session with a specialist</p>
  </div>
</div>

<form method="POST" action="/parent/appointments/book" >
  <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
  
  <div class="mb-3">
    <label for="child_id">Child *</label>
    <select id="child_id" name="child_id" required>
      <option value="">Select a child</option>
      <?php foreach ($children as $child): ?>
        <option value="<?= (int)$child['id'] ?>"><?= htmlspecialchars($child['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="mb-3">
    <label for="specialist_id">Specialist *</label>
    <select id="specialist_id" name="specialist_id" required>
      <option value="">Select a specialist</option>
      <?php foreach ($specialists as $spec): ?>
        <option value="<?= (int)$spec['id'] ?>"><?= htmlspecialchars($spec['name']) ?> — <?= htmlspecialchars($spec['title'] ?? '') ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div >
    <div class="mb-3">
      <label for="date">Date *</label>
      <input type="date" id="date" name="date" required min="<?= date('Y-m-d') ?>">
    </div>
    <div class="mb-3">
      <label for="time">Time *</label>
      <input type="time" id="time" name="time" required>
    </div>
    <div class="mb-3">
      <label for="duration">Duration (minutes)</label>
      <select id="duration" name="duration">
        <option value="30">30 min</option>
        <option value="45">45 min</option>
        <option value="60">60 min</option>
      </select>
    </div>
  </div>

  <div class="mb-3">
    <label for="notes">Notes</label>
    <textarea id="notes" name="notes" rows="3" placeholder="Any specific concerns or topics you'd like to discuss..."><?= htmlspecialchars($old['notes'] ?? '') ?></textarea>
  </div>

  <div class="d-flex gap-2 align-items-center">
    <a href="/parent/appointments" class="btn btn-outline-secondary">Cancel</a>
    <button type="submit" class="btn btn-primary">Book Appointment</button>
  </div>
</form>
