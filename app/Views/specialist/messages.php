<div class="dash-header">
  <div>
    <h1>Messages</h1>
    <p>Communicate with parents</p>
  </div>
  <div class="dash-header-actions">
    <button class="dash-btn dash-btn-primary" data-bs-toggle="modal" data-bs-target="#newMessageModal">
      <i class="fas fa-plus"></i> New Message
    </button>
  </div>
</div>

<div id="newMessageModal" class="modal fade" tabindex="-1" aria-labelledby="newMessageLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title fs-5" id="newMessageLabel">Send New Message</h3>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="/specialist/messages/send">
        <div class="modal-body">
          <input type="hidden" name="_csrf_token" value="<?= \App\Core\Session::csrf_token() ?>">
          <div class="mb-3">
            <label for="parent_select" class="form-label">Select Parent</label>
            <select id="parent_select" name="receiver_id" class="form-select" required>
              <option value="">-- Choose a parent --</option>
              <?php foreach ($parents as $parent): ?>
                <option value="<?= (int)$parent['id'] ?>"><?= htmlspecialchars($parent['name']) ?> (<?= htmlspecialchars($parent['email']) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="new_subject" class="form-label">Subject</label>
            <input type="text" id="new_subject" name="subject" class="form-control" required placeholder="Message subject">
          </div>
          <div class="mb-3">
            <label for="new_body" class="form-label">Message</label>
            <textarea id="new_body" name="body" rows="5" class="form-control" required placeholder="Type your message..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="dash-btn dash-btn-outline" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="dash-btn dash-btn-primary">Send Message</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="row row-cols-1 row-cols-md-2 g-3">
  <div class="card">
    <h3><i class="fas fa-inbox"></i> Inbox (<?= count($inbox) ?>)</h3>
    <?php if (!empty($inbox)): ?>
      <div class="message-list">
        <?php foreach ($inbox as $msg): ?>
          <a href="/specialist/messages/thread/<?= (int)$msg['sender_id'] ?>" class="message-item <?= !$msg['is_read'] ? 'unread' : '' ?>">
            <div class="msg-sender"><?= htmlspecialchars($msg['sender_name']) ?></div>
            <div class="msg-subject"><?= htmlspecialchars($msg['subject']) ?></div>
            <div class="msg-date"><?= htmlspecialchars($msg['created_at']) ?></div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="text-muted py-2">No messages.</p>
    <?php endif; ?>
  </div>

  <div class="card">
    <h3><i class="fas fa-users"></i> Conversations</h3>
    <?php if (!empty($partners)): ?>
      <div class="message-list">
        <?php foreach ($partners as $partner): ?>
          <a href="/specialist/messages/thread/<?= (int)$partner['id'] ?>" class="message-item">
            <div class="msg-sender"><?= htmlspecialchars($partner['name']) ?></div>
            <div class="msg-subject"><?= ucfirst(htmlspecialchars($partner['role'])) ?></div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="text-muted py-2">No conversations yet.</p>
    <?php endif; ?>
  </div>
</div>

