<div class="dash-header">
  <div>
    <h1>Appointments</h1>
    <p>Manage your appointments</p>
  </div>
  <a href="/specialist/calendar" class="dash-btn dash-btn-outline"><i class="fas fa-calendar"></i> Calendar View</a>
</div>

<?php if (!empty($appointments)): ?>
<div class="table-responsive">
  <table class="table table-hover align-middle mb-0 small">
    <thead>
      <tr><th>Child</th><th>Parent</th><th>Date</th><th>Time</th><th>Status</th><th>Notes</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach ($appointments as $apt): ?>
        <tr>
          <td><?= htmlspecialchars($apt['child_name']) ?></td>
          <td><?= htmlspecialchars($apt['parent_name']) ?></td>
          <td><?= htmlspecialchars($apt['date']) ?></td>
          <td><?= htmlspecialchars(substr($apt['time'], 0, 5)) ?></td>
          <td><span class="badge <?= htmlspecialchars(['pending'=>'bg-warning text-dark','confirmed'=>'bg-success','active'=>'bg-success','completed'=>'bg-primary','cancelled'=>'bg-danger','expired'=>'bg-danger'][$apt['status']] ?? 'bg-secondary') ?>"><?= ucfirst(htmlspecialchars($apt['status'])) ?></span></td>
          <td><?= htmlspecialchars(substr($apt['notes'] ?? '', 0, 50)) ?></td>
          <td>
            <?php if ($apt['status'] === 'pending'): ?>
              <form method="POST" action="/specialist/appointments/<?= (int)$apt['id'] ?>/status" class="d-inline-flex gap-1">
                <input type="hidden" name="_csrf_token" value="<?= \App\Core\Session::csrf_token() ?>">
                <input type="hidden" name="status" value="confirmed">
                <button type="submit" class="dash-btn dash-btn-sm dash-btn-success">Confirm</button>
              </form>
              <form method="POST" action="/specialist/appointments/<?= (int)$apt['id'] ?>/cancel" class="d-inline-flex gap-1">
                <input type="hidden" name="_csrf_token" value="<?= \App\Core\Session::csrf_token() ?>">
                <button type="submit" class="dash-btn dash-btn-sm dash-btn-danger">Cancel</button>
              </form>
            <?php elseif ($apt['status'] === 'confirmed'): ?>
              <form method="POST" action="/specialist/appointments/<?= (int)$apt['id'] ?>/complete" class="d-inline">
                <input type="hidden" name="_csrf_token" value="<?= \App\Core\Session::csrf_token() ?>">
                <button type="submit" class="dash-btn dash-btn-sm dash-btn-outline">Complete</button>
              </form>
              <form method="POST" action="/specialist/appointments/<?= (int)$apt['id'] ?>/cancel" class="d-inline">
                <input type="hidden" name="_csrf_token" value="<?= \App\Core\Session::csrf_token() ?>">
                <button type="submit" class="dash-btn dash-btn-sm dash-btn-danger">Cancel</button>
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
  <p>Appointments will appear here when parents book sessions with you.</p>
</div>
<?php endif; ?>
