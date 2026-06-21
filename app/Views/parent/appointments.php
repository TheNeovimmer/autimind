<div class="dash-header">
  <div>
    <h1>Appointments</h1>
    <p>Manage your appointments</p>
  </div>
  <a href="/parent/appointments/book" class="btn btn-primary"><i class="fas fa-plus"></i> Book Appointment</a>
</div>

<?php if (!empty($appointments)): ?>
<div class="table-responsive">
  <table class="table table-hover align-middle mb-0 small">
    <thead>
      <tr><th>Child</th><th>Specialist</th><th>Date</th><th>Time</th><th>Status</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach ($appointments as $apt): ?>
        <tr>
          <td><?= htmlspecialchars($apt['child_name']) ?></td>
          <td><?= htmlspecialchars($apt['specialist_name']) ?></td>
          <td><?= htmlspecialchars($apt['date']) ?></td>
          <td><?= htmlspecialchars(substr($apt['time'], 0, 5)) ?></td>
          <td><span class="badge <?= match($apt['status']) { 'active','confirmed' => 'bg-success', 'pending' => 'bg-warning text-dark', 'cancelled','expired' => 'bg-danger', default => 'bg-primary' } ?>"><?= ucfirst(htmlspecialchars($apt['status'])) ?></span></td>
          <td>
            <?php if ($apt['status'] === 'pending' || $apt['status'] === 'confirmed'): ?>
              <a href="/parent/appointments/<?= (int)$apt['id'] ?>/reschedule" class="btn btn-sm btn-outline-secondary"><i class="fas fa-calendar-alt"></i> Reschedule</a>
              <form method="POST" action="/parent/appointments/<?= (int)$apt['id'] ?>/cancel" class="d-inline" onsubmit="return confirm('Cancel this appointment?')">
                <input type="hidden" name="_csrf_token" value="<?= \App\Core\Session::csrf_token() ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger">Cancel</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
<div class="dash-empty-state">
  <h3>No appointments</h3>
  <p>Book your first appointment with a specialist.</p>
  <a href="/parent/appointments/book" class="btn btn-primary">Book Appointment</a>
</div>
<?php endif; ?>
