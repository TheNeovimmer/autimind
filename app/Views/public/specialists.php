<section class="spec-hero">
  <div class="spec-hero-inner">
    <div class="spec-hero-top">
      <div class="spec-hero-text">
        <div class="spec-tag">
          <img src="https://static.codia.ai/image/2026-06-19/roPZL2kASM.png" alt="">
          <span>Specialists</span>
        </div>
        <h1>Meet our,<br><span class="highlight">specialists</span></h1>
        <p>Our dedicated team of certified specialists brings decades of expertise in child development, speech therapy, behavioral analysis, and occupational therapy — all working together to support your child's unique journey.</p>
      </div>
      <div class="spec-avatar-card">
        <div class="spec-avatar-stack">
          <img src="https://static.codia.ai/image/2026-06-19/2XaGCUU04n.png" alt="">
          <img src="https://static.codia.ai/image/2026-06-19/XWdKwYyskr.png" alt="">
          <img src="https://static.codia.ai/image/2026-06-19/J21ahi1mtv.png" alt="">
          <img src="https://static.codia.ai/image/2026-06-19/fZYD9yGeXL.png" alt="">
          <div class="spec-avatar-more">
            <img src="https://static.codia.ai/image/2026-06-19/pCQdXOecNH.png" alt="">
          </div>
        </div>
        <div class="spec-avatar-count">+330</div>
        <div class="spec-avatar-label">Active<br>Members</div>
      </div>
    </div>

    <div class="spec-grid">
      <?php foreach ($specialists as $spec): ?>
      <div class="spec-card">
        <div class="spec-card-image" style="background-image: url(<?= htmlspecialchars($spec['avatar'] ?? 'https://via.placeholder.com/299x386') ?>);">
          <div class="spec-card-info">
            <div>
              <div class="spec-card-name"><?= htmlspecialchars($spec['name']) ?></div>
              <div class="spec-card-role"><?= htmlspecialchars($spec['title']) ?></div>
            </div>
            <img src="https://static.codia.ai/image/2026-06-19/Oh6dBOahV5.png" alt="" class="spec-card-arrow">
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>

<section class="spec-theme">
  <div class="spec-theme-inner">
    <div class="spec-theme-header">
      <h2>Trusted Expertise, <span class="highlight">proven care</span></h2>
      <p>Backed by science. Driven by compassion. Ready to support your family.</p>
    </div>
    <div class="spec-theme-grid">
      <div class="spec-theme-card">
        <div class="spec-theme-icon-wrap">
          <i class="fas fa-lightbulb"></i>
        </div>
        <h3 class="spec-theme-title">Behavior Tips</h3>
        <p class="spec-theme-desc">Discover tips to manage challenging behaviors</p>
      </div>
      <div class="spec-theme-card">
        <div class="spec-theme-icon-wrap">
          <i class="fas fa-comments"></i>
        </div>
        <h3 class="spec-theme-title">Communication</h3>
        <p class="spec-theme-desc">Strategies to build language and social skills</p>
      </div>
      <div class="spec-theme-card">
        <div class="spec-theme-icon-wrap">
          <i class="fas fa-hand-sparkles"></i>
        </div>
        <h3 class="spec-theme-title">Sensory Play</h3>
        <p class="spec-theme-desc">Activities designed for sensory regulation</p>
      </div>
      <div class="spec-theme-card">
        <div class="spec-theme-icon-wrap">
          <i class="fas fa-book-open"></i>
        </div>
        <h3 class="spec-theme-title">Learning Tools</h3>
        <p class="spec-theme-desc">Resources to support cognitive development</p>
      </div>
    </div>
  </div>
</section>
