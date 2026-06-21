<div class="dash-header">
  <div>
    <h1>Appointments</h1>
    <p>All appointments across the platform</p>
  </div>
</div>

<?php if (!empty($appointments)): ?>
<div class="table-responsive">
  <table class="table table-hover align-middle mb-0 small">
    <thead>
      <tr><th>Child</th><th>Parent</th><th>Specialist</th><th>Date</th><th>Time</th><th>Status</th><th>Notes</th></tr>
    </thead>
    <tbody>
      <?php foreach ($appointments as $apt): ?>
        <tr>
          <td><?= htmlspecialchars($apt['child_name']) ?></td>
          <td><?= htmlspecialchars($apt['parent_name']) ?></td>
          <td><?= htmlspecialchars($apt['specialist_name']) ?></td>
          <td><?= htmlspecialchars($apt['date']) ?></td>
          <td><?= htmlspecialchars(substr($apt['time'], 0, 5)) ?></td>
          <td><span class="badge status-<?= htmlspecialchars($apt['status']) ?>"><?= ucfirst(htmlspecialchars($apt['status'])) ?></span></td>
          <td><?= htmlspecialchars(substr($apt['notes'] ?? '', 0, 60)) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
<div class="dash-empty-state"><h3>No appointments</h3><p>No appointments have been booked yet.</p></div>
<?php endif; ?>
