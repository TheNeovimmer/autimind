<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo">
      <img src="https://static.codia.ai/image/2026-06-19/6vuxJTHMOw.png" alt="AutiMind">
    </div>
    <h1>Forgot Password</h1>
    <p class="auth-subtitle">Enter your email and we'll send you a reset link</p>

    <?php if (!empty($errors)): ?>
      <div class="auth-form"><div class="form-group"><span class="field-error"><?= htmlspecialchars($errors['email'][0] ?? '') ?></span></div></div>
    <?php endif; ?>

    <form method="POST" action="/forgot-password" class="auth-form">
      <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(\App\Core\Session::csrf_token()) ?>">
      <div class="form-group">
        <div class="input-wrap">
          <i class="fas fa-envelope"></i>
          <input type="email" name="email" placeholder="Email" required>
        </div>
      </div>
      <button type="submit" class="btn-primary">Send Reset Link</button>
    </form>
    <p class="auth-footer-text"><a href="/login">Back to Login</a></p>
  </div>
</div>
