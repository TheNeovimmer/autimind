<div class="dash-header">
  <div>
    <h1>Messages</h1>
    <p>Communicate with specialists</p>
  </div>
</div>

<div class="row row-cols-1 row-cols-md-2 g-3">
  <div class="card">
    <h3><i class="fas fa-inbox"></i> Inbox (<?= count($inbox) ?>)</h3>
    <?php if (!empty($inbox)): ?>
      <div class="message-list">
        <?php foreach ($inbox as $msg): ?>
          <a href="/parent/messages/thread/<?= (int)$msg['sender_id'] ?>" class="message-item <?= !$msg['is_read'] ? 'unread' : '' ?>">
            <div class="msg-sender"><?= htmlspecialchars($msg['sender_name']) ?></div>
            <div class="msg-subject"><?= htmlspecialchars($msg['subject']) ?></div>
            <div class="msg-date"><?= htmlspecialchars($msg['created_at']) ?></div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="text-muted py-2">No messages yet.</p>
    <?php endif; ?>
  </div>

  <div class="card">
    <h3><i class="fas fa-paper-plane"></i> Sent (<?= count($sent) ?>)</h3>
    <?php if (!empty($sent)): ?>
      <div class="message-list">
        <?php foreach ($sent as $msg): ?>
          <a href="/parent/messages/thread/<?= (int)$msg['receiver_id'] ?>" class="message-item">
            <div class="msg-sender">To: <?= htmlspecialchars($msg['receiver_name']) ?></div>
            <div class="msg-subject"><?= htmlspecialchars($msg['subject']) ?></div>
            <div class="msg-date"><?= htmlspecialchars($msg['created_at']) ?></div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="text-muted py-2">No sent messages.</p>
    <?php endif; ?>
  </div>
</div>
