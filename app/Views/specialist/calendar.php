<div class="dash-header">
  <div>
    <h1>Calendar</h1>
    <p><?= htmlspecialchars($monthName) ?></p>
  </div>
  <a href="/specialist/appointments" class="btn btn-outline"><i class="fas fa-list"></i> List View</a>
</div>

<div class="calendar-nav">
  <a href="?year=<?= $prevYear ?>&month=<?= $prevMonth ?>" class="btn btn-outline">&larr; Previous</a>
  <h2><?= htmlspecialchars($monthName) ?></h2>
  <a href="?year=<?= $nextYear ?>&month=<?= $nextMonth ?>" class="btn btn-outline">Next &rarr;</a>
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

<style>
.calendar-nav { display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem; }
.calendar-nav h2 { margin:0; font-size:1.25rem; }
.calendar-grid { background:var(--card-bg,#fff); border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.08); }
.calendar-header { display:grid; grid-template-columns:repeat(7,1fr); background:var(--bg-muted,#f9fafb); border-bottom:1px solid var(--border,#e5e7eb); }
.calendar-header div { padding:0.75rem 0.5rem; text-align:center; font-weight:600; font-size:0.8rem; text-transform:uppercase; color:#6b7280; }
.calendar-body { display:grid; grid-template-columns:repeat(7,1fr); }
.calendar-cell { min-height:100px; padding:0.4rem; border-right:1px solid var(--border,#e5e7eb); border-bottom:1px solid var(--border,#e5e7eb); font-size:0.8rem; }
.calendar-cell:nth-child(7n) { border-right:none; }
.calendar-empty { background:var(--bg-muted,#f9fafb); }
.calendar-day-number { font-weight:700; font-size:0.9rem; margin-bottom:0.25rem; }
.today .calendar-day-number { background:var(--primary,#4f46e5); color:#fff; width:28px; height:28px; display:flex; align-items:center; justify-content:center; border-radius:50%; }
.has-appointments { background:#f0fdf4; }
.calendar-appts { margin-top:0.25rem; }
.calendar-appt-count { display:inline-block; background:var(--primary,#4f46e5); color:#fff; font-size:0.7rem; padding:0.1rem 0.4rem; border-radius:4px; font-weight:600; }
.calendar-appt-children { font-size:0.7rem; color:#374151; margin-top:0.2rem; line-height:1.3; }
.calendar-view-link { display:inline-block; margin-top:0.2rem; font-size:0.7rem; color:var(--primary,#4f46e5); text-decoration:underline; }
.text-muted { color:#9ca3af; }
</style>
