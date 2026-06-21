<div class="dash-header">
  <div>
    <h1>Appointments</h1>
    <p>Manage your appointments</p>
  </div>
  <a href="/specialist/calendar" class="btn btn-outline"><i class="fas fa-calendar"></i> Calendar View</a>
</div>

<?php if (\App\Core\Session::hasFlash('success')): ?>
  <div class="flash-success"><?= \App\Core\Session::getFlash('success') ?></div>
<?php endif; ?>
<?php if (\App\Core\Session::hasFlash('error')): ?>
  <div class="flash-error"><?= \App\Core\Session::getFlash('error') ?></div>
<?php endif; ?>

<?php if (!empty($appointments)): ?>
<div class="table-responsive">
  <table class="dash-table">
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
          <td><span class="status-<?= htmlspecialchars($apt['status']) ?>"><?= ucfirst(htmlspecialchars($apt['status'])) ?></span></td>
          <td><?= htmlspecialchars(substr($apt['notes'] ?? '', 0, 50)) ?></td>
          <td>
            <?php if ($apt['status'] === 'pending'): ?>
              <form method="POST" action="/specialist/appointments/<?= (int)$apt['id'] ?>/status" style="display:flex;gap:0.25rem;">
                <input type="hidden" name="_csrf_token" value="<?= \App\Core\Session::csrf_token() ?>">
                <input type="hidden" name="status" value="confirmed">
                <button type="submit" class="btn-sm" style="background:#dcfce7;color:#16a34a;border:none;cursor:pointer;">Confirm</button>
              </form>
              <form method="POST" action="/specialist/appointments/<?= (int)$apt['id'] ?>/cancel" style="display:flex;gap:0.25rem;">
                <input type="hidden" name="_csrf_token" value="<?= \App\Core\Session::csrf_token() ?>">
                <button type="submit" class="btn-sm" style="background:#fee2e2;color:#dc2626;border:none;cursor:pointer;">Cancel</button>
              </form>
            <?php elseif ($apt['status'] === 'confirmed'): ?>
              <form method="POST" action="/specialist/appointments/<?= (int)$apt['id'] ?>/complete" style="display:inline;">
                <input type="hidden" name="_csrf_token" value="<?= \App\Core\Session::csrf_token() ?>">
                <button type="submit" class="btn-sm btn-outline">Complete</button>
              </form>
              <form method="POST" action="/specialist/appointments/<?= (int)$apt['id'] ?>/cancel" style="display:inline;">
                <input type="hidden" name="_csrf_token" value="<?= \App\Core\Session::csrf_token() ?>">
                <button type="submit" class="btn-sm" style="background:#fee2e2;color:#dc2626;border:none;cursor:pointer;">Cancel</button>
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
