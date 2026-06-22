<script>document.body.className = 'chatstart-page';</script>
<main class="chatstart-main">
  <div class="chatstart-inner">
    <div class="chatstart-welcome">
      <img src="https://static.codia.ai/image/2026-06-19/V1LPDh5s6v.png" alt="AI Assistant">
      <h1>Welcome Back<br><strong>Bring your ideas to life today</strong></h1>
    </div>

    <div class="chatstart-messages" id="chatstartMessages"></div>

    <div class="chatstart-chatbar">
      <div class="chatstart-chatbar-top">
        <img src="https://static.codia.ai/image/2026-06-19/8uOhZ1x1zj.png" alt="">
        <span>Ask me anything .....</span>
      </div>
      <div class="chatstart-chatbar-actions">
        <div class="chatstart-tool-group">
          <div class="chatstart-tool-icon">
            <img src="https://static.codia.ai/image/2026-06-19/AZwsiM4c0a.png" alt="">
          </div>
          <span class="chatstart-tool-btn">
            <img src="https://static.codia.ai/image/2026-06-19/bFxz6EMVLH.png" alt="">
            Tools
          </span>
          <span class="chatstart-tool-btn">
            <img src="https://static.codia.ai/image/2026-06-19/TTrCOHmopX.png" alt="">
            Deep Think
          </span>
        </div>
        <div class="chatstart-right-group">
          <span class="chatstart-tool-btn">
            <img src="https://static.codia.ai/image/2026-06-19/VtPzjcjUM8.png" alt="">
            Voice
          </span>
          <button class="chatstart-send-btn" id="chatstartSendBtn" aria-label="Send message">
            <img src="https://static.codia.ai/image/2026-06-19/jW9G0fDPwh.png" alt="">
          </button>
        </div>
      </div>
    </div>

    <div class="chatstart-features">
      <div class="chatstart-feature-card">
        <div class="chatstart-fc-top">
          <div class="chatstart-fc-icon"></div>
          <img src="https://static.codia.ai/image/2026-06-19/prnoyHAYpg.png" alt="" class="chatstart-fc-deco">
        </div>
        <div class="chatstart-fc-title">Image Generator</div>
        <div class="chatstart-fc-desc">Turn ideas into stunning visuals in seconds</div>
      </div>
      <div class="chatstart-feature-card">
        <div class="chatstart-fc-top">
          <div class="chatstart-fc-icon"></div>
          <img src="https://static.codia.ai/image/2026-06-19/mDXQNB15oS.png" alt="" class="chatstart-fc-deco">
        </div>
        <div class="chatstart-fc-title">Story Creator</div>
        <div class="chatstart-fc-desc">Craft personalized social stories for any situation</div>
      </div>
      <div class="chatstart-feature-card">
        <div class="chatstart-fc-top">
          <div class="chatstart-fc-icon"></div>
          <img src="https://static.codia.ai/image/2026-06-19/WbXshTiXQO.png" alt="" class="chatstart-fc-deco">
        </div>
        <div class="chatstart-fc-title">Voice Assistant</div>
        <div class="chatstart-fc-desc">Hands-free help with natural voice conversations</div>
      </div>
    </div>

    <div class="chatstart-benefits">
      <div class="chatstart-benefit">
        <div class="chatstart-benefit-icon"><i class="fas fa-clock"></i></div>
        <strong>24/7</strong>
        <span>Always Available</span>
      </div>
      <div class="chatstart-benefit">
        <div class="chatstart-benefit-icon"><i class="fas fa-chart-line"></i></div>
        <strong>10 K+</strong>
        <span>Questions Answered</span>
      </div>
      <div class="chatstart-benefit">
        <div class="chatstart-benefit-icon"><i class="fas fa-shield-alt"></i></div>
        <strong>Trusted &amp; Secure</strong>
        <span>Your privacy is our priority</span>
      </div>
    </div>
  </div>
</main>

<input type="hidden" id="chatstartCsrf" value="<?= htmlspecialchars($csrf_token) ?>">

<script>
document.addEventListener('DOMContentLoaded', function() {
  const chatMessages = document.getElementById('chatstartMessages');
  const sendBtn = document.getElementById('chatstartSendBtn');
  const chatbarTop = document.querySelector('.chatstart-chatbar-top');

  const input = document.createElement('input');
  input.type = 'text';
  input.className = 'chatstart-input-field';
  input.placeholder = 'Ask me anything .....';
  input.style.cssText = 'border:none;background:transparent;font-size:18px;color:#fff;outline:none;flex:1;font-family:inherit;';
  chatbarTop.querySelector('span').style.display = 'none';
  chatbarTop.appendChild(input);
  input.focus();

  input.addEventListener('keypress', function(e) {
    if (e.key === 'Enter' && input.value.trim()) {
      sendMessage(input.value.trim());
      input.value = '';
    }
  });

  sendBtn.addEventListener('click', function() {
    if (input.value.trim()) {
      sendMessage(input.value.trim());
      input.value = '';
    }
  });

  function sendMessage(message) {
    addMessage(message, 'user');
    const currentToken = document.getElementById('chatstartCsrf').value;

    fetch('/chatbot/message', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({
        _csrf_token: currentToken,
        message: message
      })
    })
    .then(r => r.json())
    .then(data => {
      if (data.csrf_token) {
        document.getElementById('chatstartCsrf').value = data.csrf_token;
      }
      addMessage(data.response || 'Sorry, something went wrong.', 'bot');
    })
    .catch(() => {
      addMessage("I'm having trouble connecting right now. Please try again in a moment.", 'bot');
    });

    scrollToBottom();
  }

  function addMessage(text, sender) {
    const bubble = document.createElement('div');
    bubble.className = 'chatstart-bubble';

    if (sender === 'user') {
      bubble.style.alignSelf = 'flex-end';
      const img = document.createElement('img');
      img.src = 'https://static.codia.ai/image/2026-06-19/S0qSHuGe2M.png';
      img.alt = '';
      img.className = 'chatstart-indicator';
      bubble.appendChild(img);
    } else {
      bubble.style.alignSelf = 'flex-start';
      const img = document.createElement('img');
      img.src = 'https://static.codia.ai/image/2026-06-19/bpSpf00Nne.png';
      img.alt = '';
      bubble.appendChild(img);
    }

    const textNode = document.createTextNode(text);
    bubble.appendChild(textNode);
    chatMessages.appendChild(bubble);
    scrollToBottom();
  }

  function scrollToBottom() {
    setTimeout(() => {
      chatMessages.scrollTop = chatMessages.scrollHeight;
    }, 50);
  }

  scrollToBottom();
});
</script>
