<div class="dash-header-premium">
  <h1>Parent Dashboard</h1>
  <p>Welcome, <?= htmlspecialchars(\App\Core\Session::get('user_name')) ?>!</p>
</div>

<?php if (!empty($children)): ?>

<div class="dash-stats-grid dash-stagger">
  <div class="dash-stat-card">
    <div class="stat-icon children"><i class="fas fa-child"></i></div>
    <div class="stat-value"><?= (int)$childCount ?></div>
    <div class="stat-label">Children</div>
    <div class="stat-footer">
      <a href="/parent/children" class="stat-link">Manage →</a>
    </div>
  </div>
  <div class="dash-stat-card">
    <div class="stat-icon appointments"><i class="fas fa-calendar-check"></i></div>
    <div class="stat-value"><?= count($upcomingAppointments) ?></div>
    <div class="stat-label">Upcoming Appointments</div>
    <div class="stat-footer">
      <a href="/parent/appointments" class="stat-link">View All →</a>
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
      <a href="/parent/messages" class="stat-link">View →</a>
    </div>
  </div>
  <div class="dash-stat-card">
    <div class="stat-icon subscriptions"><i class="fas fa-clipboard-list"></i></div>
    <div class="stat-value"><?= $latestQuiz ? (int)$latestQuiz['total_score'] . '/50' : '--' ?></div>
    <div class="stat-label">Latest Screening</div>
    <div class="stat-footer">
      <?php if ($latestQuiz): ?>
        <span class="stat-detail">Risk: <?= ucfirst(htmlspecialchars($latestQuiz['risk_level'])) ?></span>
        <a href="/parent/quiz" class="stat-link">Details →</a>
      <?php else: ?>
        <a href="/parent/quiz" class="stat-link">Start →</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="charts-section">
  <div class="chart-card chart-card-wide">
    <div class="chart-card-header">
      <h3><i class="fas fa-chart-line"></i> Quiz Score History</h3>
      <span class="chart-badge"><?= htmlspecialchars($children[0]['name']) ?></span>
    </div>
    <div class="chart-body chart-body-line">
      <canvas id="chartQuizHistory"></canvas>
    </div>
  </div>
  <div class="chart-card">
    <div class="chart-card-header">
      <h3><i class="fas fa-chart-pie"></i> Appointment Status</h3>
    </div>
    <div class="chart-body chart-body-doughnut">
      <canvas id="chartParentApptStatus"></canvas>
    </div>
  </div>
  <div class="chart-card chart-card-wide">
    <div class="chart-card-header">
      <h3><i class="fas fa-chart-bar"></i> Activity by Category</h3>
      <span class="chart-badge">All children</span>
    </div>
    <div class="chart-body chart-body-bar">
      <canvas id="chartActivityCat"></canvas>
    </div>
  </div>
</div>

<?php if (!empty($upcomingAppointments)): ?>
<div class="chart-card chart-card-full">
  <div class="chart-card-header">
    <h3><i class="fas fa-calendar-day"></i> Upcoming Appointments</h3>
  </div>
  <div class="appointment-list-compact">
    <?php foreach ($upcomingAppointments as $apt): ?>
      <div class="compact-item">
        <span class="compact-date"><?= htmlspecialchars($apt['date']) ?></span>
        <span class="compact-time"><?= htmlspecialchars(substr($apt['time'], 0, 5)) ?></span>
        <span class="compact-name"><?= htmlspecialchars($apt['child_name'] ?? '') ?></span>
        <span style="color: var(--text-secondary); font-size: 0.78rem;">with <?= htmlspecialchars($apt['specialist_name'] ?? '') ?></span>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<?php if (!empty($insights)): ?>
<div class="chart-card chart-card-full" style="margin-top: 1.25rem;">
  <div class="chart-card-header">
    <h3><i class="fas fa-lightbulb"></i> AI Insights</h3>
  </div>
  <div class="dash-insights-grid">
    <?php foreach ($insights as $i): ?>
      <div class="dash-insight-card">
        <div class="dash-insight-icon">
          <i class="fas <?= htmlspecialchars($i['icon'] ?? 'fa-brain') ?>"></i>
        </div>
        <div class="dash-insight-content">
          <div class="dash-insight-title"><?= htmlspecialchars($i['title']) ?></div>
          <div class="dash-insight-desc"><?= htmlspecialchars($i['description']) ?></div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<?php else: ?>
<div class="dash-empty-state">
  <i class="fas fa-child dash-empty-icon"></i>
  <h2>Welcome to AutiMind!</h2>
  <p>Get started by adding your first child to begin tracking their progress.</p>
  <a href="/parent/children/add" class="dash-btn dash-btn-primary">Add Your First Child</a>
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

  <?php if (!empty($quizScoreHistory)): ?>
  // Quiz Score History line
  <?php
  $qDays = []; $qScores = [];
  foreach ($quizScoreHistory as $q) { $qDays[] = date('M j', strtotime($q['day'])); $qScores[] = (int)$q['total_score']; }
  ?>
  new Chart(document.getElementById('chartQuizHistory'), {
    type: 'line',
    data: {
      labels: <?= json_encode($qDays) ?>,
      datasets: [{ data: <?= json_encode($qScores) ?>, borderColor: purple, backgroundColor: hex(purple, 0.1), fill: true, tension: 0.4, pointBackgroundColor: purple, pointRadius: 3, pointHoverRadius: 6, borderWidth: 2.5 }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false }, tooltip: tooltipOpts },
      scales: {
        x: { ticks: { color: '#9ca3af', font: { size: 11, family: 'Inter' } }, grid: { display: false } },
        y: { ticks: { color: '#9ca3af', font: { size: 11 } }, grid: { color: '#f3f4f6' }, beginAtZero: true, max: 50 }
      },
      animation: { duration: 800 }
    }
  });
  <?php endif; ?>

  <?php if (!empty($apptStatuses)): ?>
  <?php
  $psLabels = []; $psCounts = []; $psColors = [];
  $psMap = ['pending'=>'#f59e0b','confirmed'=>'#22c55e','completed'=>'#3b82f6','cancelled'=>'#f43f5e'];
  foreach ($apptStatuses as $ps) { $psLabels[] = ucfirst($ps['status']); $psCounts[] = (int)$ps['count']; $psColors[] = $psMap[$ps['status']] ?? '#9ca3af'; }
  ?>
  new Chart(document.getElementById('chartParentApptStatus'), {
    type: 'doughnut',
    data: { labels: <?= json_encode($psLabels) ?>, datasets: [{ data: <?= json_encode($psCounts) ?>, backgroundColor: <?= json_encode($psColors) ?>, borderWidth: 0 }] },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { position: 'bottom', labels: { color: '#6b7280', padding: 12, font: { size: 11, family: 'Inter' }, usePointStyle: true, pointStyle: 'circle' } } },
      cutout: '68%',
      animation: { animateRotate: true, duration: 1000 }
    }
  });
  <?php endif; ?>

  <?php if (!empty($activityByCat)): ?>
  <?php
  $acLabels = []; $acCounts = [];
  foreach ($activityByCat as $ac) { $acLabels[] = $ac['category']; $acCounts[] = (int)$ac['count']; }
  $acPalette = [teal, blue, amber, rose, purple, green];
  $acColors = array_slice($acPalette, 0, count($acLabels));
  ?>
  new Chart(document.getElementById('chartActivityCat'), {
    type: 'bar',
    data: { labels: <?= json_encode($acLabels) ?>, datasets: [{ data: <?= json_encode($acCounts) ?>, backgroundColor: <?= json_encode($acColors) ?>, borderRadius: 6, borderSkipped: false }] },
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
  <?php endif; ?>
});
</script>
