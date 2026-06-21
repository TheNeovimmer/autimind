<div class="dash-header">
  <div>
    <h1>Calendar</h1>
    <p><?= htmlspecialchars($monthName) ?></p>
  </div>
  <a href="/specialist/appointments" class="btn btn-outline-secondary"><i class="fas fa-list"></i> List View</a>
</div>

<div class="calendar-nav">
  <a href="?year=<?= $prevYear ?>&month=<?= $prevMonth ?>" class="btn btn-outline-secondary">&larr; Previous</a>
  <h2><?= htmlspecialchars($monthName) ?></h2>
  <a href="?year=<?= $nextYear ?>&month=<?= $nextMonth ?>" class="btn btn-outline-secondary">Next &rarr;</a>
</div>

<div class="calendar-grid">
  <div class="calendar-header">
    <div>Sun</div><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div>
  </div>
  <div class="calendar-body">
    <?php for ($i = 0; $i < $startDow; $i++): ?>
      <div class="calendar-cell calendar-empty"></div>
    <?php endfor; ?>

    <?php for ($day = 1; $day <= $daysInMonth; $day++): ?>
      <?php
      $hasAppts = isset($dayAppts[$day]);
      $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
      ?>
      <div class="calendar-cell <?= $hasAppts ? 'has-appointments' : '' ?> <?= $day === (int)date('j') && $month === (int)date('n') && $year === (int)date('Y') ? 'today' : '' ?>">
        <div class="calendar-day-number"><?= $day ?></div>
        <?php if ($hasAppts): ?>
          <div class="calendar-appts">
            <span class="calendar-appt-count"><?= $dayAppts[$day]['count'] ?> appointment<?= $dayAppts[$day]['count'] > 1 ? 's' : '' ?></span>
            <div class="calendar-appt-children">
              <?= htmlspecialchars(implode(', ', array_slice($dayAppts[$day]['children'], 0, 2))) ?>
              <?php if (count($dayAppts[$day]['children']) > 2): ?>
                <span class="text-muted">+<?= count($dayAppts[$day]['children']) - 2 ?> more</span>
              <?php endif; ?>
            </div>
            <a href="/specialist/appointments?date=<?= $dateStr ?>" class="calendar-view-link">View</a>
          </div>
        <?php endif; ?>
      </div>
    <?php endfor; ?>
  </div>
</div>

