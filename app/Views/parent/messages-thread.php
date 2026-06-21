<div class="dash-header">
  <div>
    <h1>Messages with <?= htmlspecialchars($partner['name']) ?></h1>
  </div>
  <a href="/parent/messages" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<div class="card message-thread">
  <?php if (!empty($thread)): ?>
    <?php foreach ($thread as $msg): ?>
      <div class="thread-message <?= $msg['sender_id'] === \App\Core\Session::get('user_id') ? 'own' : 'other' ?>">
        <div class="thread-header">
          <strong><?= htmlspecialchars($msg['sender_name']) ?></strong>
          <small><?= htmlspecialchars($msg['created_at']) ?></small>
        </div>
        <div class="thread-subject"><?= htmlspecialchars($msg['subject']) ?></div>
        <div class="thread-body"><?= nl2br(htmlspecialchars($msg['body'])) ?></div>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <p class="dash-empty">No messages in this conversation.</p>
  <?php endif; ?>
</div>

<form method="POST" action="/parent/messages/reply" >
  <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
  <input type="hidden" name="receiver_id" value="<?= (int)$partner['id'] ?>">
  <input type="hidden" name="subject" value="Re: <?= htmlspecialchars(isset($thread[0]) ? $thread[0]['subject'] : 'Message') ?>">

  <div class="mb-3">
    <label for="body">Reply</label>
    <textarea id="body" name="body" rows="3" required placeholder="Type your reply..."></textarea>
  </div>

  <button type="submit" class="btn btn-primary">Send Reply</button>
</form>
