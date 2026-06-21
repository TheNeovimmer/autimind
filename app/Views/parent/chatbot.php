<div class="dash-header">
  <div>
    <h1>AI Chat Assistant</h1>
    <p>Ask questions about autism, screening, and features</p>
  </div>
</div>

<div class="card chatbot-container">
  <div class="chatbot-messages" id="chatMessages">
    <?php if (!empty($history)): ?>
      <?php foreach (array_reverse($history) as $entry): ?>
        <div class="chat-message <?= htmlspecialchars($entry['sender']) ?>">
          <div class="chat-bubble">
            <?php if ($entry['sender'] === 'bot'): ?>
              <i class="fas fa-robot chat-icon"></i>
            <?php endif; ?>
            <span><?= nl2br(htmlspecialchars($entry['message'])) ?></span>
            <small class="chat-time"><?= htmlspecialchars($entry['created_at']) ?></small>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="chat-message bot">
        <div class="chat-bubble">
          <i class="fas fa-robot chat-icon"></i>
          <span>Hello! I'm AutiMind assistant. Ask me about autism, screening, progress tracking, appointments, or our features!</span>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <div class="chatbot-input">
    <input type="hidden" id="csrfToken" value="<?= htmlspecialchars($csrf_token) ?>">
    <input type="text" id="chatInput" placeholder="Type your message..." autofocus>
    <button id="chatSendBtn" class="dash-btn dash-btn-primary px-4"><i class="fas fa-paper-plane me-1"></i> Ask</button>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const chatInput = document.getElementById('chatInput');
  const chatSend = document.getElementById('chatSendBtn');
  const chatMessages = document.getElementById('chatMessages');

  function scrollToBottom() {
    chatMessages.scrollTop = chatMessages.scrollHeight;
  }

  function addMessage(text, sender) {
    const div = document.createElement('div');
    div.className = 'chat-message ' + sender;
    div.innerHTML = '<div class="chat-bubble">' + (sender === 'bot' ? '<i class="fas fa-robot chat-icon"></i>' : '') + '<span>' + text + '</span></div>';
    chatMessages.appendChild(div);
    scrollToBottom();
  }

  function sendMessage() {
    const message = chatInput.value.trim();
    if (!message) return;

    addMessage(message, 'user');
    chatInput.value = '';
    chatInput.disabled = true;
    chatSend.disabled = true;

    fetch('/parent/chatbot/message', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({
        _csrf_token: document.getElementById('csrfToken').value,
        message: message
      })
    })
    .then(r => r.json())
    .then(data => {
      addMessage(data.response, 'bot');
    })
    .catch(() => {
      addMessage('Sorry, something went wrong. Please try again.', 'bot');
    })
    .finally(() => {
      chatInput.disabled = false;
      chatSend.disabled = false;
      chatInput.focus();
    });
  }

  chatSend.addEventListener('click', sendMessage);
  chatInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') sendMessage();
  });

  scrollToBottom();
});
</script>
