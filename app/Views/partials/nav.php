<nav class="navbar">
  <div class="nav-logo">
    <a href="/"><img src="https://static.codia.ai/image/2026-06-19/6vuxJTHMOw.png" alt="AutiMind"></a>
  </div>
  <button class="nav-toggle" aria-label="Toggle navigation">
    <span></span><span></span><span></span>
  </button>
  <ul class="nav-links">
    <li><a href="/" class="<?= $_SERVER['REQUEST_URI'] === '/' ? 'active' : '' ?>">Home</a></li>
    <li><a href="/about" class="<?= str_starts_with($_SERVER['REQUEST_URI'], '/about') ? 'active' : '' ?>">About</a></li>
    <li><a href="/program" class="<?= str_starts_with($_SERVER['REQUEST_URI'], '/program') ? 'active' : '' ?>">Program</a></li>
    <li><a href="/espaceenfant" class="<?= str_starts_with($_SERVER['REQUEST_URI'], '/espaceenfant') ? 'active' : '' ?>">Children</a></li>
    <li><a href="/espaceparent" class="<?= str_starts_with($_SERVER['REQUEST_URI'], '/espaceparent') ? 'active' : '' ?>">Parents</a></li>
    <li><a href="/specialists" class="<?= str_starts_with($_SERVER['REQUEST_URI'], '/specialists') ? 'active' : '' ?>">Specialists</a></li>
    <li><a href="/pricing" class="<?= str_starts_with($_SERVER['REQUEST_URI'], '/pricing') ? 'active' : '' ?>">Pricing</a></li>
    <li><a href="/contact" class="<?= str_starts_with($_SERVER['REQUEST_URI'], '/contact') ? 'active' : '' ?>">Contact</a></li>
    <?php if (\App\Core\Session::has('user_id')): ?>
      <li class="nav-btn-item"><a href="/logout">Logout</a></li>
    <?php else: ?>
      <li class="nav-btn-item"><a href="/signup">Get Started</a></li>
    <?php endif; ?>
  </ul>
  <?php if (\App\Core\Session::has('user_id')): ?>
    <a href="/logout" class="btn-signup">Logout</a>
  <?php else: ?>
    <a href="/signup" class="btn-signup">Get Started</a>
  <?php endif; ?>
</nav>
