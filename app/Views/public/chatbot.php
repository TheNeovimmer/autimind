<section class="chatbot-hero">
  <div class="chatbot-hero-inner">
    <h1>Your AI Assistant,<br><span class="highlight">Here to help</span></h1>
    <p>Got questions about autism, services, or support?<br>Our AI chatbot is available 24/7 to give you quick, reliable answers and guidance.</p>
    <?php if (\App\Core\Session::has('user_id')): ?>
    <a href="/chatbotstart" class="chatbot-cta">
      Start Chatting
      <img src="https://static.codia.ai/image/2026-06-19/71zAHtYsYZ.png" alt="">
    </a>
    <?php else: ?>
    <a href="/signup" class="chatbot-cta">
      Create Account to Start Chatting
      <img src="https://static.codia.ai/image/2026-06-19/71zAHtYsYZ.png" alt="">
    </a>
    <?php endif; ?>
  </div>
</section>
