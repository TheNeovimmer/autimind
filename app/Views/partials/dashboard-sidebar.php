<?php
$role = \App\Core\Session::get('role');
$currentUri = $_SERVER['REQUEST_URI'];
?>
<aside class="dash-sidebar" id="dashSidebar">
  <div class="dash-sidebar-logo">
    <a href="/"><img src="https://static.codia.ai/image/2026-06-19/6vuxJTHMOw.png" alt="AutiMind"></a>
  </div>
  <nav class="dash-sidebar-nav">
    <?php if ($role === 'parent'): ?>
      <div class="dash-nav-section-label">Main</div>
      <a href="/parent/dashboard" class="dash-nav-item <?= $currentUri === '/parent/dashboard' ? 'active' : '' ?>">
        <i class="fas fa-th-large"></i><span>Dashboard</span>
      </a>
      <a href="/parent/children" class="dash-nav-item <?= str_starts_with($currentUri, '/parent/children') ? 'active' : '' ?>">
        <i class="fas fa-child"></i><span>Children</span>
      </a>
      <a href="/parent/quiz" class="dash-nav-item <?= str_starts_with($currentUri, '/parent/quiz') ? 'active' : '' ?>">
        <i class="fas fa-clipboard-list"></i><span>Screening Quiz</span>
      </a>
      <a href="/parent/progress" class="dash-nav-item <?= str_starts_with($currentUri, '/parent/progress') ? 'active' : '' ?>">
        <i class="fas fa-chart-line"></i><span>Progress</span>
      </a>
      <div class="dash-nav-section-label">Engage</div>
      <a href="/parent/specialists" class="dash-nav-item <?= str_starts_with($currentUri, '/parent/specialists') ? 'active' : '' ?>">
        <i class="fas fa-user-md"></i><span>Specialists</span>
      </a>
      <a href="/parent/appointments" class="dash-nav-item <?= str_starts_with($currentUri, '/parent/appointments') ? 'active' : '' ?>">
        <i class="fas fa-calendar-check"></i><span>Appointments</span>
      </a>
      <a href="/parent/messages" class="dash-nav-item <?= str_starts_with($currentUri, '/parent/messages') ? 'active' : '' ?>">
        <i class="fas fa-envelope"></i><span>Messages</span>
      </a>
      <a href="/parent/chatbot" class="dash-nav-item <?= str_starts_with($currentUri, '/parent/chatbot') ? 'active' : '' ?>">
        <i class="fas fa-robot"></i><span>AI Chat</span>
      </a>
      <div class="dash-nav-section-label">Account</div>
      <a href="/parent/subscription" class="dash-nav-item <?= str_starts_with($currentUri, '/parent/subscription') ? 'active' : '' ?>">
        <i class="fas fa-credit-card"></i><span>Subscription</span>
      </a>
      <a href="/parent/settings" class="dash-nav-item <?= $currentUri === '/parent/settings' ? 'active' : '' ?>">
        <i class="fas fa-cog"></i><span>Settings</span>
      </a>
    <?php elseif ($role === 'specialist'): ?>
      <div class="dash-nav-section-label">Main</div>
      <a href="/specialist/dashboard" class="dash-nav-item <?= $currentUri === '/specialist/dashboard' ? 'active' : '' ?>"><i class="fas fa-th-large"></i><span>Dashboard</span></a>
      <a href="/specialist/patients" class="dash-nav-item <?= str_starts_with($currentUri, '/specialist/patients') ? 'active' : '' ?>"><i class="fas fa-users"></i><span>Patients</span></a>
      <div class="dash-nav-section-label">Schedule</div>
      <a href="/specialist/appointments" class="dash-nav-item <?= str_starts_with($currentUri, '/specialist/appointments') ? 'active' : '' ?>"><i class="fas fa-calendar-check"></i><span>Appointments</span></a>
      <a href="/specialist/calendar" class="dash-nav-item <?= str_starts_with($currentUri, '/specialist/calendar') ? 'active' : '' ?>"><i class="fas fa-calendar-alt"></i><span>Calendar</span></a>
      <a href="/specialist/schedule" class="dash-nav-item <?= $currentUri === '/specialist/schedule' ? 'active' : '' ?>"><i class="fas fa-clock"></i><span>Schedule</span></a>
      <div class="dash-nav-section-label">Communication</div>
      <a href="/specialist/messages" class="dash-nav-item <?= str_starts_with($currentUri, '/specialist/messages') ? 'active' : '' ?>"><i class="fas fa-envelope"></i><span>Messages</span></a>
      <div class="dash-nav-section-label">Account</div>
      <a href="/specialist/settings" class="dash-nav-item <?= $currentUri === '/specialist/settings' ? 'active' : '' ?>"><i class="fas fa-cog"></i><span>Settings</span></a>
    <?php elseif ($role === 'admin'): ?>
      <div class="dash-nav-section-label">Main</div>
      <a href="/admin/dashboard" class="dash-nav-item <?= $currentUri === '/admin/dashboard' ? 'active' : '' ?>"><i class="fas fa-th-large"></i><span>Dashboard</span></a>
      <div class="dash-nav-section-label">Management</div>
      <a href="/admin/users" class="dash-nav-item <?= str_starts_with($currentUri, '/admin/users') ? 'active' : '' ?>"><i class="fas fa-users-cog"></i><span>Users</span></a>
      <a href="/admin/specialists" class="dash-nav-item <?= str_starts_with($currentUri, '/admin/specialists') ? 'active' : '' ?>"><i class="fas fa-user-md"></i><span>Specialists</span></a>
      <div class="dash-nav-section-label">Content</div>
      <a href="/admin/quiz" class="dash-nav-item <?= str_starts_with($currentUri, '/admin/quiz') ? 'active' : '' ?>"><i class="fas fa-clipboard-list"></i><span>Quiz</span></a>
      <a href="/admin/activities" class="dash-nav-item <?= str_starts_with($currentUri, '/admin/activities') ? 'active' : '' ?>"><i class="fas fa-gamepad"></i><span>Activities</span></a>
      <a href="/admin/faq" class="dash-nav-item <?= str_starts_with($currentUri, '/admin/faq') ? 'active' : '' ?>"><i class="fas fa-question-circle"></i><span>FAQ</span></a>
      <a href="/admin/chatbot" class="dash-nav-item <?= str_starts_with($currentUri, '/admin/chatbot') ? 'active' : '' ?>"><i class="fas fa-robot"></i><span>Chatbot</span></a>
      <div class="dash-nav-section-label">Operations</div>
      <a href="/admin/appointments" class="dash-nav-item <?= str_starts_with($currentUri, '/admin/appointments') ? 'active' : '' ?>"><i class="fas fa-calendar"></i><span>Appointments</span></a>
      <a href="/admin/messages" class="dash-nav-item <?= str_starts_with($currentUri, '/admin/messages') ? 'active' : '' ?>"><i class="fas fa-envelope"></i><span>Messages</span></a>
      <a href="/admin/subscriptions" class="dash-nav-item <?= str_starts_with($currentUri, '/admin/subscriptions') ? 'active' : '' ?>"><i class="fas fa-credit-card"></i><span>Subscriptions</span></a>
      <a href="/admin/contacts" class="dash-nav-item <?= str_starts_with($currentUri, '/admin/contacts') ? 'active' : '' ?>"><i class="fas fa-id-card"></i><span>Contacts</span></a>
      <div class="dash-nav-section-label">Analytics</div>
      <a href="/admin/progress" class="dash-nav-item <?= str_starts_with($currentUri, '/admin/progress') ? 'active' : '' ?>"><i class="fas fa-chart-bar"></i><span>Progress</span></a>
      <div class="dash-nav-section-label">Account</div>
      <a href="/admin/settings" class="dash-nav-item <?= $currentUri === '/admin/settings' ? 'active' : '' ?>"><i class="fas fa-cog"></i><span>Settings</span></a>
    <?php endif; ?>
  </nav>
  <div class="dash-sidebar-footer">
    <a href="/logout" class="dash-nav-item dash-nav-logout"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
  </div>
</aside>
<div class="dash-sidebar-backdrop" id="dashSidebarBackdrop"></div>
