<nav class="dash-topbar navbar navbar-light bg-white border-bottom px-3">
  <div class="d-flex align-items-center gap-3">
    <button class="navbar-toggler dash-sidebar-toggle border-0 p-1" type="button" id="dashSidebarToggle">
      <i class="fas fa-bars"></i>
    </button>
    <h2 class="h5 mb-0"><?= htmlspecialchars($title ?? 'Dashboard') ?></h2>
  </div>
  <div class="d-flex align-items-center">
    <div class="dropdown">
      <button class="btn dropdown-toggle d-flex align-items-center gap-2 border-0 bg-transparent p-1" data-bs-toggle="dropdown" aria-expanded="false">
        <span class="dash-user-name"><?= htmlspecialchars(\App\Core\Session::get('user_name')) ?></span>
        <div class="dash-user-avatar">
          <?php
          $userId = \App\Core\Session::get('user_id');
          $user = $userId ? \App\Models\User::findById($userId) : null;
          ?>
          <?php if ($user && !empty($user['avatar'])): ?>
            <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar">
          <?php else: ?>
            <?= strtoupper(substr(\App\Core\Session::get('user_name'), 0, 1)) ?>
          <?php endif; ?>
        </div>
      </button>
      <ul class="dropdown-menu dropdown-menu-end shadow-sm">
        <?php
        $role = \App\Core\Session::get('role');
        $dashLinks = [
          'parent' => '/parent/dashboard',
          'specialist' => '/specialist/dashboard',
          'admin' => '/admin/dashboard',
        ];
        $dashUrl = $dashLinks[$role] ?? '/parent/dashboard';
        ?>
        <li><a class="dropdown-item" href="<?= $dashUrl ?>"><i class="fas fa-th-large me-2"></i> Dashboard</a></li>
        <li><a class="dropdown-item" href="/<?= $role ?>/settings"><i class="fas fa-cog me-2"></i> Settings</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item text-danger" href="/logout"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
      </ul>
    </div>
  </div>
</nav>