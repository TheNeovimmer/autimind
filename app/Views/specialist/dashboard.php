<div class="dash-header">
  <div>
    <h1>Specialist Dashboard</h1>
    <p>Welcome, <?= htmlspecialchars(\App\Core\Session::get('user_name')) ?></p>
  </div>
</div>

<div class="row row-cols-1 row-cols-md-2 g-3">
  <div class="card">
    <div class="card-header">
      <h3><i class="fas fa-users"></i> Total Patients</h3>
      <span class="badge bg-primary-subtle text-primary"><?= (int)$totalPatients ?></span>
    </div>
    <a href="/specialist/patients" class="dash-link">View Patients →</a>
  </div>

  <div class="card">
    <div class="card-header">
      <h3><i class="fas fa-calendar-check"></i> Appointments</h3>
      <span class="badge bg-primary-subtle text-primary"><?= (int)$totalAppointments ?> total</span>
    </div>
    <?php if ($pendingAppointments > 0): ?>
      <p><span class="pending-badge"><?= (int)$pendingAppointments ?> pending confirmation</span></p>
    <?php endif; ?>
    <a href="/specialist/appointments" class="dash-link">Manage Appointments →</a>
  </div>

  <div class="card">
    <div class="card-header">
      <h3><i class="fas fa-arrow-right"></i> Upcoming</h3>
    </div>
    <?php if (!empty($upcoming)): ?>
      <ul class="appointment-list-compact">
        <?php foreach (array_slice($upcoming, 0, 5) as $apt): ?>
          <li>
            <strong><?= htmlspecialchars($apt['date']) ?></strong> at <?= htmlspecialchars(substr($apt['time'], 0, 5)) ?>
            <br><small>with <?= htmlspecialchars($apt['child_name']) ?></small>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="dash-empty">No upcoming appointments.</p>
    <?php endif; ?>
  </div>

  <div class="card">
    <div class="card-header">
      <h3><i class="fas fa-envelope"></i> Messages</h3>
      <?php if ($unreadMessages > 0): ?>
        <span class="badge bg-warning-subtle text-warning-emphasis"><?= (int)$unreadMessages ?> unread</span>
      <?php endif; ?>
    </div>
    <p><?= $unreadMessages > 0 ? 'You have ' . $unreadMessages . ' unread messages.' : 'No unread messages.' ?></p>
    <a href="/specialist/messages" class="dash-link">Go to Messages →</a>
  </div>
</div>
