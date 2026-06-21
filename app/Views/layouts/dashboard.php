<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title ?? 'Dashboard') ?> - AutiMind</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700;800&family=Inter:wght@400;500;600&family=Outfit:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/styles.css">
  <link rel="stylesheet" href="/assets/css/dashboard.css">
</head>
<body class="dashboard-body">
  <div id="session-timeout-data" data-lifetime="3600" data-warn="300" data-activity="<?= $_SESSION['_last_activity'] ?? time() ?>"></div>
  <div class="dashboard-wrapper">
    <?php \App\Core\View::renderPartial('dashboard-sidebar'); ?>
    <main class="dashboard-main">
      <?php \App\Core\View::renderPartial('dashboard-topbar'); ?>
      <div class="dashboard-content">
        <div class="container-fluid p-4">
          <?= $content ?>
        </div>
      </div>
      <?php \App\Core\View::renderPartial('toast'); ?>
    </main>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="/assets/js/app.js"></script>
</body>
</html>
