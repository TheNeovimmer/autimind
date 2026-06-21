<div class="admin-welcome">
  <div class="admin-welcome-text">
    <h1>Dashboard</h1>
    <p>Good to have you back, <?= htmlspecialchars(\App\Core\Session::get('user_name')) ?></p>
  </div>
  <div class="admin-welcome-actions">
    <a href="/admin/users/add" class="btn-primary btn-sm"><i class="fas fa-plus"></i> Add User</a>
    <a href="/admin/quiz/add" class="btn-outline btn-sm"><i class="fas fa-plus"></i> Add Question</a>
  </div>
</div>

<!-- Stat Cards -->
<div class="stat-cards">
  <div class="stat-card-item">
    <div class="stat-card-icon users"><i class="fas fa-users"></i></div>
    <div class="stat-card-body">
      <span class="stat-card-value"><?= (int)$totalUsers ?></span>
      <span class="stat-card-label">Total Users</span>
    </div>
    <div class="stat-card-footer">
      <span class="stat-card-detail"><?= (int)$totalParents ?> parents, <?= (int)$totalSpecialists ?> specialists</span>
      <a href="/admin/users" class="stat-card-link">View →</a>
    </div>
  </div>
  <div class="stat-card-item">
    <div class="stat-card-icon appointments"><i class="fas fa-calendar-check"></i></div>
    <div class="stat-card-body">
      <span class="stat-card-value"><?= (int)$totalAppointments ?></span>
      <span class="stat-card-label">Appointments</span>
    </div>
    <div class="stat-card-footer">
      <?php if ($pendingAppointments > 0): ?>
        <span class="stat-card-detail pending"><?= (int)$pendingAppointments ?> pending</span>
      <?php else: ?>
        <span class="stat-card-detail">All handled</span>
      <?php endif; ?>
      <a href="/admin/appointments" class="stat-card-link">View →</a>
    </div>
  </div>
  <div class="stat-card-item">
    <div class="stat-card-icon children"><i class="fas fa-child"></i></div>
    <div class="stat-card-body">
      <span class="stat-card-value"><?= (int)$totalChildren ?></span>
      <span class="stat-card-label">Children</span>
    </div>
    <div class="stat-card-footer">
      <span class="stat-card-detail"><?= (int)$totalChildren ?> registered</span>
    </div>
  </div>
  <div class="stat-card-item">
    <div class="stat-card-icon subscriptions"><i class="fas fa-crown"></i></div>
    <div class="stat-card-body">
      <span class="stat-card-value"><?= (int)$totalSubscriptions ?></span>
      <span class="stat-card-label">Subscriptions</span>
    </div>
    <div class="stat-card-footer">
      <span class="stat-card-detail"><?= (int)$activeSubscriptions ?> active</span>
      <a href="/admin/subscriptions" class="stat-card-link">Manage →</a>
    </div>
  </div>
  <div class="stat-card-item">
    <div class="stat-card-icon messages"><i class="fas fa-envelope"></i></div>
    <div class="stat-card-body">
      <span class="stat-card-value"><?= (int)$unreadMessages ?></span>
      <span class="stat-card-label">Unread Messages</span>
    </div>
    <div class="stat-card-footer">
      <?php if ($unreadMessages > 0): ?>
        <span class="stat-card-detail unread"><?= (int)$unreadMessages ?> need attention</span>
      <?php else: ?>
        <span class="stat-card-detail">All clear</span>
      <?php endif; ?>
      <a href="/admin/messages" class="stat-card-link">View →</a>
    </div>
  </div>
  <div class="stat-card-item">
    <div class="stat-card-icon contacts"><i class="fas fa-phone"></i></div>
    <div class="stat-card-body">
      <span class="stat-card-value"><?= (int)$unreadContacts ?></span>
      <span class="stat-card-label">Unread Contacts</span>
    </div>
    <div class="stat-card-footer">
      <?php if ($unreadContacts > 0): ?>
        <span class="stat-card-detail unread"><?= (int)$unreadContacts ?> need reply</span>
      <?php else: ?>
        <span class="stat-card-detail">All read</span>
      <?php endif; ?>
      <a href="/admin/contacts" class="stat-card-link">View →</a>
    </div>
  </div>
</div>

<!-- Charts -->
<div class="charts-section">
  <div class="chart-card chart-card-wide">
    <div class="chart-card-header">
      <h3><i class="fas fa-chart-line"></i> New Registrations (7 days)</h3>
    </div>
    <div class="chart-body">
      <canvas id="chartRegistrations"></canvas>
    </div>
  </div>
  <div class="chart-card chart-card-wide">
    <div class="chart-card-header">
      <h3><i class="fas fa-chart-line"></i> Quiz Attempts (7 days)</h3>
    </div>
    <div class="chart-body">
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

  // --- Doughnut defaults ---
  const doughnutOpts = {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { position: 'bottom', labels: { color: '#6b7280', padding: 16, font: { size: 12, family: 'Inter' }, usePointStyle: true, pointStyle: 'circle' } } },
    cutout: '68%',
    animation: { animateRotate: true, duration: 1000 }
  };

  // Role Distribution
  <?php
  $roleLabels = []; $roleCounts = [];
  foreach ($roleDistribution as $r) { $roleLabels[] = ucfirst($r['role']); $roleCounts[] = (int)$r['count']; }
  ?>
  new Chart(document.getElementById('chartRoleDistribution'), {
    type: 'doughnut',
    data: { labels: <?= json_encode($roleLabels) ?>, datasets: [{ data: <?= json_encode($roleCounts) ?>, backgroundColor: [purple, teal, blue], borderWidth: 0 }] },
    options: doughnutOpts
  });

  // Appointment Status
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

  // --- Line chart helper ---
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

  // Registrations
  <?php
  $regDays = []; $regCounts = [];
  foreach ($registrations as $r) { $regDays[] = date('M j', strtotime($r['day'])); $regCounts[] = (int)$r['count']; }
  ?>
  lineChart('chartRegistrations', <?= json_encode($regDays) ?>, <?= json_encode($regCounts) ?>, purple, 'New Users');

  // Quiz Attempts
  <?php
  $quizDays = []; $quizCounts = [];
  foreach ($quizAttempts as $a) { $quizDays[] = date('M j', strtotime($a['day'])); $quizCounts[] = (int)$a['count']; }
  ?>
  lineChart('chartQuizAttempts', <?= json_encode($quizDays) ?>, <?= json_encode($quizCounts) ?>, teal, 'Attempts');

  // Subscriptions by Plan (bar)
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
