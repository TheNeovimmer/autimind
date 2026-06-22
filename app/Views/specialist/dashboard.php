<div class="dash-header-premium">
  <h1>Specialist Dashboard</h1>
  <p>Welcome, <?= htmlspecialchars(\App\Core\Session::get('user_name')) ?></p>
</div>

<div class="dash-stats-grid dash-stagger">
  <div class="dash-stat-card">
    <div class="stat-icon users"><i class="fas fa-users"></i></div>
    <div class="stat-value"><?= (int)$totalPatients ?></div>
    <div class="stat-label">Total Patients</div>
    <div class="stat-footer">
      <a href="/specialist/patients" class="stat-link">View Patients →</a>
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
        <span class="stat-detail">Total scheduled</span>
      <?php endif; ?>
      <a href="/specialist/appointments" class="stat-link">Manage →</a>
    </div>
  </div>
  <div class="dash-stat-card">
    <div class="stat-icon messages"><i class="fas fa-envelope"></i></div>
    <div class="stat-value"><?= (int)$unreadMessages ?></div>
    <div class="stat-label">Unread Messages</div>
    <div class="stat-footer">
      <?php if ($unreadMessages > 0): ?>
        <span class="stat-detail unread"><?= (int)$unreadMessages ?> need reply</span>
      <?php else: ?>
        <span class="stat-detail">All clear</span>
      <?php endif; ?>
      <a href="/specialist/messages" class="stat-link">View →</a>
    </div>
  </div>
  <div class="dash-stat-card">
    <div class="stat-icon children"><i class="fas fa-child"></i></div>
    <div class="stat-value"><?= !empty($upcoming) ? count($upcoming) : 0 ?></div>
    <div class="stat-label">Upcoming</div>
    <div class="stat-footer">
      <span class="stat-detail">Next appointments</span>
    </div>
  </div>
</div>

<div class="charts-section">
  <div class="chart-card chart-card-wide">
    <div class="chart-card-header">
      <h3><i class="fas fa-chart-bar"></i> Weekly Appointments</h3>
      <span class="chart-badge">Last 8 weeks</span>
    </div>
    <div class="chart-body chart-body-bar">
      <canvas id="chartWeeklyAppts"></canvas>
    </div>
  </div>
  <div class="chart-card">
    <div class="chart-card-header">
      <h3><i class="fas fa-chart-pie"></i> Status</h3>
    </div>
    <div class="chart-body chart-body-doughnut">
      <canvas id="chartApptStatus"></canvas>
    </div>
  </div>
  <div class="chart-card">
    <div class="chart-card-header">
      <h3><i class="fas fa-chart-bar"></i> Age Distribution</h3>
      <span class="chart-badge">Patients</span>
    </div>
    <div class="chart-body chart-body-bar">
      <canvas id="chartAgeDist"></canvas>
    </div>
  </div>
  <div class="chart-card chart-card-wide">
    <div class="chart-card-header">
      <h3><i class="fas fa-chart-bar"></i> Risk Level Distribution</h3>
      <span class="chart-badge">Screening</span>
    </div>
    <div class="chart-body chart-body-bar">
      <canvas id="chartRiskDist"></canvas>
    </div>
  </div>
</div>

<?php if (!empty($upcoming)): ?>
<div class="chart-card chart-card-full">
  <div class="chart-card-header">
    <h3><i class="fas fa-calendar-day"></i> Upcoming Appointments</h3>
    <span class="chart-badge">Next <?= min(count($upcoming), 5) ?> of <?= count($upcoming) ?></span>
  </div>
  <div class="appointment-list-compact">
    <?php foreach (array_slice($upcoming, 0, 5) as $apt): ?>
      <div class="compact-item">
        <span class="compact-date"><?= htmlspecialchars($apt['date']) ?></span>
        <span class="compact-time"><?= htmlspecialchars(substr($apt['time'], 0, 5)) ?></span>
        <span class="compact-name"><?= htmlspecialchars($apt['child_name']) ?></span>
        <span class="compact-parent"><?= htmlspecialchars($apt['parent_name'] ?? '') ?></span>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

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

  const tooltipOpts = {
    backgroundColor: '#fff',
    titleColor: '#1e1e2e',
    bodyColor: '#6b7280',
    borderColor: '#e5e7eb',
    borderWidth: 1,
    padding: 10,
    cornerRadius: 8
  };

  function barChart(id, labels, data, colors) {
    new Chart(document.getElementById(id), {
      type: 'bar',
      data: { labels: labels, datasets: [{ data: data, backgroundColor: colors, borderRadius: 6, borderSkipped: false }] },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false }, tooltip: tooltipOpts },
        scales: {
          x: { ticks: { color: '#9ca3af', font: { size: 11, family: 'Inter' } }, grid: { display: false } },
          y: { ticks: { color: '#9ca3af', font: { size: 11 } }, grid: { color: '#f3f4f6' }, beginAtZero: true }
        },
        animation: { duration: 600 }
      }
    });
  }

  // Weekly Appointments
  <?php
  $wLabels = []; $wCounts = [];
  foreach ($weeklyAppts as $w) { $wLabels[] = date('M j', strtotime($w['week_start'])); $wCounts[] = (int)$w['count']; }
  $wColors = array_fill(0, count($wLabels), '#6c0090');
  ?>
  barChart('chartWeeklyAppts', <?= json_encode($wLabels) ?>, <?= json_encode($wCounts) ?>, <?= json_encode($wColors) ?>);

  // Age Distribution
  <?php
  $aLabels = []; $aCounts = [];
  $agePalette = ['0-3'=>'#14b8a6', '4-6'=>'#3b82f6', '7-9'=>'#f59e0b', '10-12'=>'#f43f5e', '13+'=>'#6c0090'];
  foreach ($ageDist as $a) { $aLabels[] = $a['age_range']; $aCounts[] = (int)$a['count']; }
  $aColors = array_map(fn($l) => $agePalette[$l] ?? '#9ca3af', $aLabels);
  ?>
  barChart('chartAgeDist', <?= json_encode($aLabels) ?>, <?= json_encode($aCounts) ?>, <?= json_encode($aColors) ?>);

  // Risk Level Distribution
  <?php
  $rLabels = []; $rCounts = [];
  $riskPalette = ['low'=>'#22c55e', 'moderate'=>'#f59e0b', 'high'=>'#f43f5e'];
  foreach ($riskDist as $r) { $rLabels[] = ucfirst($r['risk_level']); $rCounts[] = (int)$r['count']; }
  $rColors = array_map(fn($l) => $riskPalette[strtolower($l)] ?? '#9ca3af', $rLabels);
  ?>
  barChart('chartRiskDist', <?= json_encode($rLabels) ?>, <?= json_encode($rCounts) ?>, <?= json_encode($rColors) ?>);

  // Appointment Status doughnut
  <?php
  $stLabels = []; $stCounts = []; $stColors = [];
  $statusColors = ['pending'=>'#f59e0b','confirmed'=>'#22c55e','completed'=>'#3b82f6','cancelled'=>'#f43f5e'];
  foreach ($statusCounts as $st) { $stLabels[] = ucfirst($st['status']); $stCounts[] = (int)$st['count']; $stColors[] = $statusColors[$st['status']] ?? '#9ca3af'; }
  ?>
  new Chart(document.getElementById('chartApptStatus'), {
    type: 'doughnut',
    data: { labels: <?= json_encode($stLabels) ?>, datasets: [{ data: <?= json_encode($stCounts) ?>, backgroundColor: <?= json_encode($stColors) ?>, borderWidth: 0 }] },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { position: 'bottom', labels: { color: '#6b7280', padding: 12, font: { size: 11, family: 'Inter' }, usePointStyle: true, pointStyle: 'circle' } } },
      cutout: '68%',
      animation: { animateRotate: true, duration: 1000 }
    }
  });
});
</script>
