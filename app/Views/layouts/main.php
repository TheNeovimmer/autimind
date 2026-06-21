<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title ?? 'AutiMind') ?> - AutiMind</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700;800&family=Inter:wght@400;500;600&family=Outfit:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
  <?php if (\App\Core\Session::has('user_id')): ?>
    <div id="session-timeout-data" data-lifetime="3600" data-warn="300" data-activity="<?= $_SESSION['_last_activity'] ?? time() ?>"></div>
  <?php endif; ?>
  <?php \App\Core\View::renderPartial('nav'); ?>
  <?= $content ?>
  <?php \App\Core\View::renderPartial('toast'); ?>
  <?php \App\Core\View::renderPartial('footer'); ?>
  <div class="custom-cursor"></div>
  <div class="cursor-follower"></div>
  <script src="https://unpkg.com/lenis@1.1.18/dist/lenis.min.js"></script>
  <script src="/assets/js/app.js"></script>
</body>
</html>
