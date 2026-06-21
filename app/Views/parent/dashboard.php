<div class="dash-header">
  <div>
    <h1>Parent Dashboard</h1>
    <p>Welcome back, <?= htmlspecialchars(\App\Core\Session::get('user_name')) ?>!</p>
  </div>
</div>

<?php if (!empty($children)): ?>
<div class="dash-grid">
  <div class="dash-card">
    <div class="dash-card-header">
      <h3><i class="fas fa-child"></i> Children</h3>
      <span class="dash-badge"><?= count($children) ?></span>
    </div>
    <ul class="child-list-compact">
      <?php foreach ($children as $child): ?>
        <li>
          <span class="child-avatar-sm"><?= strtoupper(substr(htmlspecialchars($child['name']), 0, 1)) ?></span>
          <span><?= htmlspecialchars($child['name']) ?></span>
          <?php if ($child['age']): ?><small>(<?= (int)$child['age'] ?> yrs)</small><?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
    <a href="/parent/children" class="dash-link">Manage Children →</a>
  </div>

  <div class="dash-card">
    <div class="dash-card-header">
      <h3><i class="fas fa-calendar-check"></i> Upcoming Appointments</h3>
      <span class="dash-badge"><?= count($upcomingAppointments) ?></span>
    </div>
    <?php if (!empty($upcomingAppointments)): ?>
      <ul class="appointment-list-compact">
        <?php foreach ($upcomingAppointments as $apt): ?>
          <li>
            <strong><?= htmlspecialchars($apt['date']) ?></strong> at <?= htmlspecialchars(substr($apt['time'], 0, 5)) ?>
            <br><small><?= htmlspecialchars($apt['child_name']) ?> with <?= htmlspecialchars($apt['specialist_name']) ?></small>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="dash-empty">No upcoming appointments.</p>
    <?php endif; ?>
    <a href="/parent/appointments" class="dash-link">View All →</a>
  </div>

  <div class="dash-card">
    <div class="dash-card-header">
      <h3><i class="fas fa-envelope"></i> Messages</h3>
      <?php if ($unreadMessages > 0): ?>
        <span class="dash-badge dash-badge-warning"><?= $unreadMessages ?> unread</span>
      <?php endif; ?>
    </div>
    <p><?= $unreadMessages > 0 ? 'You have ' . $unreadMessages . ' unread message(s).' : 'No unread messages.' ?></p>
    <a href="/parent/messages" class="dash-link">Go to Messages →</a>
  </div>

  <div class="dash-card">
    <div class="dash-card-header">
      <h3><i class="fas fa-clipboard-list"></i> Latest Screening</h3>
    </div>
    <?php if ($latestQuiz): ?>
      <p>Score: <strong><?= (int)$latestQuiz['total_score'] ?>/50</strong> · Risk: <span class="risk-<?= htmlspecialchars($latestQuiz['risk_level']) ?>"><?= ucfirst(htmlspecialchars($latestQuiz['risk_level'])) ?></span></p>
      <a href="/parent/quiz" class="dash-link">View Details →</a>
    <?php else: ?>
      <p class="dash-empty">No screening completed yet.</p>
      <a href="/parent/quiz" class="dash-link">Start Screening →</a>
    <?php endif; ?>
  </div>
</div>

<?php if (!empty($insights)): ?>
<div class="dash-section">
  <h2><i class="fas fa-lightbulb"></i> AI Insights</h2>
  <div class="dash-grid dash-grid-2">
    <?php foreach ($insights as $insight): ?>
      <div class="dash-card insight-card insight-<?= htmlspecialchars($insight['type']) ?>">
        <div class="insight-icon"><i class="fas <?= htmlspecialchars($insight['icon']) ?>"></i></div>
        <div class="insight-content">
          <h4><?= htmlspecialchars($insight['title']) ?></h4>
          <p><?= htmlspecialchars($insight['description']) ?></p>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<?php else: ?>
<div class="dash-empty-state">
  <i class="fas fa-child" style="font-size: 3rem; color: var(--primary);"></i>
  <h2>Welcome to AutiMind!</h2>
  <p>Get started by adding your first child to begin tracking their progress.</p>
  <a href="/parent/children/add" class="btn btn-primary">Add Your First Child</a>
</div>
<?php endif; ?>
