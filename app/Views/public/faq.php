<section class="faq-hero fade-in">
  <div class="section-tag" style="justify-content:center">
    <span>FAQ</span>
  </div>
  <h1>Frequently asked <span class="highlight">questions</span></h1>
  <p>Everything you need to know about AutiMind. Can't find what you're looking for? <a href="/contact" style="color:var(--secondary)">Contact us</a>.</p>
</section>

<div class="faq-filters fade-in">
  <button class="faq-filter active" data-category="all">All</button>
  <button class="faq-filter" data-category="general">General</button>
  <button class="faq-filter" data-category="features">Features</button>
  <button class="faq-filter" data-category="pricing">Pricing</button>
  <button class="faq-filter" data-category="technical">Technical</button>
</div>

<div class="faq-list fade-in">
  <?php foreach ($faqItems as $item): ?>
  <div class="faq-item" data-category="<?= htmlspecialchars($item['category']) ?>">
    <button class="faq-question">
      <?= htmlspecialchars($item['question']) ?>
      <i class="fas fa-plus"></i>
    </button>
    <div class="faq-answer"><?= htmlspecialchars($item['answer']) ?></div>
  </div>
  <?php endforeach; ?>
</div>
