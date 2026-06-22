<div class="dash-header-premium">
  <div>
    <h1>Patients</h1>
    <p>Children assigned to you</p>
  </div>
  <a href="/specialist/patients/export" class="dash-btn dash-btn-outline"><i class="fas fa-download"></i> Export CSV</a>
</div>

<?php if (!empty($patients)): ?>
<div class="dash-table-wrapper">
  <table class="dash-table">
    <thead>
      <tr><th>Child</th><th>Parent</th><th>Age</th><th>Diagnosis</th><th>Appointments</th><th>Last Screening</th><th></th></tr>
    </thead>
    <tbody>
      <?php foreach ($patients as $p): ?>
        <tr>
          <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
          <td><?= htmlspecialchars($p['parent_name']) ?></td>
          <td><?= $p['age'] ? (int)$p['age'] . ' yrs' : '-' ?></td>
          <td><?= htmlspecialchars($p['diagnosis_status'] ?? '-') ?></td>
          <td><?= (int)$p['appointment_count'] ?></td>
          <td><?= htmlspecialchars($p['last_screening'] ?? '-') ?></td>
          <td><a href="/specialist/patients/<?= (int)$p['id'] ?>" class="dash-btn dash-btn-sm dash-btn-outline">View</a></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
<div class="dash-empty-state">
  <h3>No patients yet</h3>
  <p>Patients will appear here once parents book appointments with you.</p>
</div>
<?php endif; ?>
