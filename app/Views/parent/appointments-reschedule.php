<div class="dash-header-premium">
  <div>
    <h1>Reschedule Appointment</h1>
    <p>Change the date and time for your appointment</p>
  </div>
  <a href="/parent/appointments" class="dash-btn dash-btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<div class="card">
  <h3>Current Appointment Details</h3>
  <p><strong>Date:</strong> <?= htmlspecialchars($appointment['date']) ?></p>
  <p><strong>Time:</strong> <?= htmlspecialchars(substr($appointment['time'], 0, 5)) ?></p>
  <?php if (!empty($appointment['notes'])): ?>
    <p><strong>Notes:</strong> <?= nl2br(htmlspecialchars($appointment['notes'])) ?></p>
  <?php endif; ?>
</div>

<form method="POST" action="/parent/appointments/<?= (int)$appointment['id'] ?>/reschedule" >
  <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

  <div >
    <div class="dash-field">
      <label for="date" >New Date *</label>
      <input type="date" id="date" name="date"  value="<?= htmlspecialchars($old['date'] ?? $appointment['date']) ?>" required>
    </div>
    <div class="dash-field">
      <label for="time" >New Time *</label>
      <input type="time" id="time" name="time"  value="<?= htmlspecialchars($old['time'] ?? substr($appointment['time'], 0, 5)) ?>" required>
    </div>
  </div>

  <div class="dash-field">
    <label for="notes" >Notes</label>
    <textarea id="notes" name="notes" rows="3" ><?= htmlspecialchars($old['notes'] ?? $appointment['notes'] ?? '') ?></textarea>
  </div>

  <div class="form-actions">
    <a href="/parent/appointments" class="dash-btn dash-btn-outline">Cancel</a>
    <button type="submit" class="dash-btn dash-btn-primary">Update Appointment</button>
  </div>
</form>
