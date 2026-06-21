<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo">
      <img src="https://static.codia.ai/image/2026-06-19/6vuxJTHMOw.png" alt="AutiMind">
    </div>
    <h1>Welcome Back</h1>
    <p class="auth-subtitle">Sign in to your AutiMind account</p>

    <?php if (\App\Core\Session::hasFlash('success')): ?>
      <div class="auth-form"><div class="form-group" style="text-align:center;color:#22c55e;font-size:14px;"><?= \App\Core\Session::getFlash('success') ?></div></div>
    <?php endif; ?>
    <?php if (\App\Core\Session::hasFlash('error')): ?>
      <div class="auth-form"><div class="form-group" style="text-align:center;color:#ff6b6b;font-size:14px;"><?= \App\Core\Session::getFlash('error') ?></div></div>
    <?php endif; ?>

    <form method="POST" action="/login" class="auth-form">
      <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(\App\Core\Session::csrf_token()) ?>">
      <div class="form-group">
        <div class="input-wrap">
          <i class="fas fa-envelope"></i>
          <input type="email" name="email" placeholder="Email" required
                 value="<?= htmlspecialchars($old['email'] ?? '') ?>">
        </div>
        <?php if (isset($errors['email'])): ?>
          <span class="field-error"><?= htmlspecialchars($errors['email'][0]) ?></span>
        <?php endif; ?>
      </div>
      <div class="form-group">
        <div class="input-wrap">
          <i class="fas fa-lock"></i>
          <input type="password" name="password" placeholder="Password" required>
          <i class="fas fa-eye password-toggle" onclick="togglePassword(this)" style="pointer-events:auto;cursor:pointer;"></i>
        </div>
        <?php if (isset($errors['password'])): ?>
          <span class="field-error"><?= htmlspecialchars($errors['password'][0]) ?></span>
        <?php endif; ?>
      </div>
      <div class="form-row">
        <label><input type="checkbox" name="remember"> Remember me</label>
        <a href="/forgot-password">Forgot password?</a>
      </div>
      <button type="submit" class="btn-primary">Sign In</button>
    </form>
    <p class="auth-footer-text">Don't have an account? <a href="/signup">Sign Up</a></p>
  </div>
</div>
