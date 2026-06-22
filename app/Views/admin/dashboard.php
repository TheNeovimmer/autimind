<div class="admin-welcome">
  <div class="admin-welcome-text">
    <h1>Admin Dashboard</h1>
    <p>Good to have you back, <?= htmlspecialchars(\App\Core\Session::get('user_name')) ?></p>
  </div>
  <div class="admin-welcome-actions">
    <a href="/admin/users/add" class="dash-btn dash-btn-sm dash-btn-primary"><i class="fas fa-plus"></i> Add User</a>
    <a href="/admin/quiz/add" class="dash-btn dash-btn-sm dash-btn-outline"><i class="fas fa-plus"></i> Add Question</a>
  </div>
</div>

<div class="dash-stats-grid dash-stagger">
  <div class="dash-stat-card">
    <div class="stat-icon users"><i class="fas fa-users"></i></div>
    <div class="stat-value"><?= (int)$totalUsers ?></div>
    <div class="stat-label">Total Users</div>
    <div class="stat-footer">
      <span class="stat-detail"><?= (int)$totalParents ?> parents · <?= (int)$totalSpecialists ?> specialists</span>
      <a href="/admin/users" class="stat-link">View →</a>
    </div>
  </div>
  <div class="dash-stat-card">
    <div class="stat-icon appointments"><i class="fas fa-calendar-check"></i></div>
    <div class="stat-value"><?= (int)$totalAppointments ?></div>
    <div class="stat-label">Appointments</div>
    <div class="stat-footer">
      <?php if ($pendingAppointments > 0): ?>
        <span class="stat-detail pending"><?= (int)$pendingAppointments ?> pending</span>
      <?php else: ?>
        <span class="stat-detail">All handled</span>
      <?php endif; ?>
      <a href="/admin/appointments" class="stat-link">View →</a>
    </div>
  </div>
  <div class="dash-stat-card">
    <div class="stat-icon children"><i class="fas fa-child"></i></div>
    <div class="stat-value"><?= (int)$totalChildren ?></div>
    <div class="stat-label">Children</div>
    <div class="stat-footer">
      <span class="stat-detail"><?= (int)$totalChildren ?> registered</span>
    </div>
  </div>
  <div class="dash-stat-card">
    <div class="stat-icon subscriptions"><i class="fas fa-crown"></i></div>
    <div class="stat-value"><?= (int)$totalSubscriptions ?></div>
    <div class="stat-label">Subscriptions</div>
    <div class="stat-footer">
      <span class="stat-detail"><?= (int)$activeSubscriptions ?> active</span>
      <a href="/admin/subscriptions" class="stat-link">Manage →</a>
    </div>
  </div>
  <div class="dash-stat-card">
    <div class="stat-icon messages"><i class="fas fa-envelope"></i></div>
    <div class="stat-value"><?= (int)$unreadMessages ?></div>
    <div class="stat-label">Unread Messages</div>
    <div class="stat-footer">
      <?php if ($unreadMessages > 0): ?>
        <span class="stat-detail unread"><?= (int)$unreadMessages ?> need attention</span>
      <?php else: ?>
        <span class="stat-detail">All clear</span>
      <?php endif; ?>
      <a href="/admin/messages" class="stat-link">View →</a>
    </div>
  </div>
  <div class="dash-stat-card">
    <div class="stat-icon contacts"><i class="fas fa-phone"></i></div>
    <div class="stat-value"><?= (int)$unreadContacts ?></div>
    <div class="stat-label">Unread Contacts</div>
    <div class="stat-footer">
      <?php if ($unreadContacts > 0): ?>
        <span class="stat-detail unread"><?= (int)$unreadContacts ?> need reply</span>
      <?php else: ?>
        <span class="stat-detail">All read</span>
      <?php endif; ?>
      <a href="/admin/contacts" class="stat-link">View →</a>
    </div>
  </div>
</div>

<div class="charts-section">
  <div class="chart-card chart-card-wide">
    <div class="chart-card-header">
      <h3><i class="fas fa-chart-line"></i> New Registrations (7 days)</h3>
    </div>
    <div class="chart-body chart-body-line">
      <canvas id="chartRegistrations"></canvas>
    </div>
  </div>
  <div class="chart-card chart-card-wide">
    <div class="chart-card-header">
      <h3><i class="fas fa-chart-line"></i> Quiz Attempts (7 days)</h3>
    </div>
    <div class="chart-body chart-body-line">
      <canvas id="chartQuizAttempts"></canvas>
    </div>
  </div>
  <div class="chart-card">
    <div class="chart-card-header">
      <h3><i class="fas fa-chart-pie"></i> Users by Role</h3>
    </div>
    <div class="chart-body chart-body-doughnut">
      <canvas id="chartRoleDistribution"></canvas>
    </div>
  </div>
  <div class="chart-card">
    <div class="chart-card-header">
      <h3><i class="fas fa-chart-pie"></i> Appointment Status</h3>
    </div>
    <div class="chart-body chart-body-doughnut">
      <canvas id="chartAppointmentStatus"></canvas>
    </div>
  </div>
  <div class="chart-card">
    <div class="chart-card-header">
      <h3><i class="fas fa-chart-bar"></i> Subscriptions by Plan</h3>
    </div>
    <div class="chart-body chart-body-bar">
      <canvas id="chartPlans"></canvas>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const purple = '#6c0090';
  const teal = '#14b8a6';
  const amber = '#f59e0b';
  const rose = '#f43f5e';
  const blue = '#3b82f6';
  const green = '#22c55e';
  const gray = '#9ca3af';

  function hex(c, a) { return c + Math.round(a * 255).toString(16).padStart(2, '0'); }

  const doughnutOpts = {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { position: 'bottom', labels: { color: '#6b7280', padding: 16, font: { size: 12, family: 'Inter' }, usePointStyle: true, pointStyle: 'circle' } } },
    cutout: '68%',
    animation: { animateRotate: true, duration: 1000 }
  };

  <?php
  $roleLabels = []; $roleCounts = [];
  foreach ($roleDistribution as $r) { $roleLabels[] = ucfirst($r['role']); $roleCounts[] = (int)$r['count']; }
  ?>
  new Chart(document.getElementById('chartRoleDistribution'), {
    type: 'doughnut',
    data: { labels: <?= json_encode($roleLabels) ?>, datasets: [{ data: <?= json_encode($roleCounts) ?>, backgroundColor: [purple, teal, blue], borderWidth: 0 }] },
    options: doughnutOpts
  });

  <?php
  $sLabels = []; $sCounts = []; $sColors = [];
  $cm = ['pending'=>'#f59e0b','confirmed'=>'#22c55e','completed'=>'#3b82f6','cancelled'=>'#f43f5e'];
  foreach ($appointmentStatuses as $s) { $sLabels[] = ucfirst($s['status']); $sCounts[] = (int)$s['count']; $sColors[] = $cm[$s['status']] ?? '#9ca3af'; }
  ?>
  new Chart(document.getElementById('chartAppointmentStatus'), {
    type: 'doughnut',
    data: { labels: <?= json_encode($sLabels) ?>, datasets: [{ data: <?= json_encode($sCounts) ?>, backgroundColor: <?= json_encode($sColors) ?>, borderWidth: 0 }] },
    options: doughnutOpts
  });

  function lineChart(id, labels, data, color, label) {
    new Chart(document.getElementById(id), {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{ label: label, data: data, borderColor: color, backgroundColor: hex(color, 0.1), fill: true, tension: 0.4, pointBackgroundColor: color, pointRadius: 3, pointHoverRadius: 6, borderWidth: 2.5 }]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false }, tooltip: { backgroundColor: '#fff', titleColor: '#1e1e2e', bodyColor: '#6b7280', borderColor: '#e5e7eb', borderWidth: 1, padding: 10, cornerRadius: 8 } },
        scales: {
          x: { ticks: { color: '#9ca3af', font: { size: 11, family: 'Inter' } }, grid: { display: false } },
          y: { ticks: { color: '#9ca3af', font: { size: 11 }, stepSize: 1 }, grid: { color: '#f3f4f6' }, beginAtZero: true }
        },
        animation: { duration: 800 }
      }
    });
  }

  <?php
  $regDays = []; $regCounts = [];
  foreach ($registrations as $r) { $regDays[] = date('M j', strtotime($r['day'])); $regCounts[] = (int)$r['count']; }
  ?>
  lineChart('chartRegistrations', <?= json_encode($regDays) ?>, <?= json_encode($regCounts) ?>, purple, 'New Users');

  <?php
  $quizDays = []; $quizCounts = [];
  foreach ($quizAttempts as $a) { $quizDays[] = date('M j', strtotime($a['day'])); $quizCounts[] = (int)$a['count']; }
  ?>
  lineChart('chartQuizAttempts', <?= json_encode($quizDays) ?>, <?= json_encode($quizCounts) ?>, teal, 'Attempts');

  <?php
  $planLabels = []; $planCounts = [];
  foreach ($planDistribution as $p) { $planLabels[] = ucfirst($p['plan']); $planCounts[] = (int)$p['count']; }
  ?>
  new Chart(document.getElementById('chartPlans'), {
    type: 'bar',
    data: { labels: <?= json_encode($planLabels) ?>, datasets: [{ label: 'Subscriptions', data: <?= json_encode($planCounts) ?>, backgroundColor: [purple, teal, amber], borderRadius: 8, borderSkipped: false }] },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false }, tooltip: { backgroundColor: '#fff', titleColor: '#1e1e2e', bodyColor: '#6b7280', borderColor: '#e5e7eb', borderWidth: 1, padding: 10, cornerRadius: 8 } },
      scales: {
        x: { ticks: { color: '#9ca3af', font: { size: 12, family: 'Inter' } }, grid: { display: false } },
        y: { ticks: { color: '#9ca3af', font: { size: 11 }, stepSize: 1 }, grid: { color: '#f3f4f6' }, beginAtZero: true }
      },
      animation: { duration: 600 }
    }
  });
});
</script>
