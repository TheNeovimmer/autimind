# Bootstrap Dashboard Migration — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development or superpowers:executing-plans. Steps use checkbox (`- [ ]`) syntax.

**Goal:** Convert all dashboard UI (~60 PHP view files + CSS) from pure custom CSS to Bootstrap 5.3 CDN while preserving brand identity.

**Architecture:** Bootstrap 5.3.3 via CDN in dashboard layout. Refactored dashboard.css stripped of Bootstrap-covered component CSS, retaining only brand overrides + custom sidebar/topbar/chat/quiz/calendar/pricing components. Bulk pattern replacements across all role view files.

**Tech Stack:** Bootstrap 5.3.3 CDN, existing PHP views, existing custom dashboard.css refactored.

---

## File Structure

### Files Modified
- `app/Views/layouts/dashboard.php` — add Bootstrap CDN
- `app/Views/partials/dashboard-topbar.php` — convert to Bootstrap dropdown
- `public/assets/css/dashboard.css` — major refactor, remove ~800 lines of Bootstrap-covered CSS
- `app/Views/admin/*.php` (13 files) — bulk class replacements
- `app/Views/parent/*.php` (20 files) — bulk class replacements
- `app/Views/specialist/*.php` (10 files) — bulk class replacements

---

### Task 1: Add Bootstrap CDN + Refactor dashboard.css

**Files:**
- Modify: `app/Views/layouts/dashboard.php`
- Modify: `public/assets/css/dashboard.css`
- Modify: `app/Views/partials/dashboard-topbar.php`

- [ ] **Step 1: Add Bootstrap CDN to dashboard layout**

Add Bootstrap CSS before existing stylesheets, Bootstrap JS before closing body.

```diff
--- a/app/Views/layouts/dashboard.php
+++ b/app/Views/layouts/dashboard.php
   <link rel="preconnect" href="https://fonts.googleapis.com">
+  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
   <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700;800&family=Inter:wght@400;500;600&family=Outfit:wght@400;700&display=swap" rel="stylesheet">
...
   <div class="dashboard-content">
+    <div class="container-fluid p-4">
       <?= $content ?>
+    </div>
   </div>
...
   <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
+  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
   <script src="/assets/js/app.js"></script>
```

- [ ] **Step 2: Convert topbar to Bootstrap navbar + dropdown**

Replace `dashboard-topbar.php` contents:

```php
<nav class="dash-topbar navbar navbar-light bg-white border-bottom px-3">
  <div class="d-flex align-items-center gap-3">
    <button class="navbar-toggler dash-sidebar-toggle border-0 p-1" type="button">
      <i class="fas fa-bars"></i>
    </button>
    <h2 class="h5 mb-0"><?= htmlspecialchars($title ?? 'Dashboard') ?></h2>
  </div>
  <div class="d-flex align-items-center">
    <div class="dropdown">
      <button class="btn dropdown-toggle d-flex align-items-center gap-2 border-0 bg-transparent p-1" data-bs-toggle="dropdown" aria-expanded="false">
        <span class="dash-user-name"><?= htmlspecialchars(\App\Core\Session::get('user_name')) ?></span>
        <div class="dash-user-avatar">
          <?php
          $userId = \App\Core\Session::get('user_id');
          $user = $userId ? \App\Models\User::findById($userId) : null;
          ?>
          <?php if ($user && !empty($user['avatar'])): ?>
            <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar">
          <?php else: ?>
            <?= strtoupper(substr(\App\Core\Session::get('user_name'), 0, 1)) ?>
          <?php endif; ?>
        </div>
      </button>
      <ul class="dropdown-menu dropdown-menu-end shadow-sm">
        <?php
        $role = \App\Core\Session::get('role');
        $dashLinks = [
          'parent' => '/parent/dashboard',
          'specialist' => '/specialist/dashboard',
          'admin' => '/admin/dashboard',
        ];
        $dashUrl = $dashLinks[$role] ?? '/parent/dashboard';
        ?>
        <li><a class="dropdown-item" href="<?= $dashUrl ?>"><i class="fas fa-th-large me-2"></i> Dashboard</a></li>
        <li><a class="dropdown-item" href="/<?= $role ?>/settings"><i class="fas fa-cog me-2"></i> Settings</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item text-danger" href="/logout"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
      </ul>
    </div>
  </div>
</nav>
```

Also update the sidebar toggle JS to target `.navbar-toggler` instead of `.dash-sidebar-toggle`.

- [ ] **Step 3: Refactor dashboard.css — remove Bootstrap-covered CSS, keep brand overrides + custom components**

Read the current dashboard.css, then rewrite it. Remove all CSS for: buttons, cards, tables, forms, badges, pagination, modals, utility classes (`.d-flex`, `.gap-*`, `.mb-*`, `.mt-*`, `.text-*`). Keep CSS for: sidebar, topbar layout, stat-card icons, chat, specialist grid, quiz, calendar, pricing, child/appointment/message lists, animations, empty state, avatar upload, insight cards.

Add Bootstrap CSS variable overrides at top:

```css
:root {
  --bs-primary: #6c0090;
  --bs-primary-rgb: 108, 0, 144;
  --bs-link-color: #6c0090;
  --bs-link-hover-color: #4a0060;
  --bs-body-font-family: 'Inter', sans-serif;
  --bs-border-radius: 8px;
  --bs-border-radius-lg: 12px;
}
```

- [ ] **Step 4: Verify layout renders**

Run: `php -l app/Views/layouts/dashboard.php && php -l app/Views/partials/dashboard-topbar.php && php -l app/Views/partials/dashboard-sidebar.php`
Expected: No syntax errors

---

### Task 2: Bulk Convert Tables

**Files:** All admin/parent/specialist view files with `.dash-table`

- [ ] **Step 1: Replace .dash-table classes across all views**

```bash
cd /home/neovimmer/Documents/autimind
# Replace .dash-table with Bootstrap table
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/class="dash-table"/class="table table-striped table-hover align-middle mb-0"/g' {} +
```

- [ ] **Step 2: Verify replacements**

Run: `grep -rn 'class="dash-table"' app/Views/admin app/Views/parent app/Views/specialist`
Expected: No matches

---

### Task 3: Bulk Convert Cards

**Files:** All view files with `.dash-card`, `.dash-card-header`, `.dash-card-footer`

- [ ] **Step 1: Replace .dash-card classes**

```bash
cd /home/neovimmer/Documents/autimind
# Replace .dash-card with .card
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/class="dash-card"/class="card"/g' {} +
# Replace .dash-card-header with .card-header
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/class="dash-card-header"/class="card-header"/g' {} +
# Replace .dash-card-footer with .card-footer  
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/class="dash-card-footer"/class="card-footer"/g' {} +
```

- [ ] **Step 2: Wrap bare card content in .card-body**

For cards that have content directly as children (not in a header/footer), wrap the non-header/footer children in `<div class="card-body">`. This needs careful per-file handling — skip this step and handle during per-file verification.

---

### Task 4: Bulk Convert Buttons

**Files:** All view files with button classes

- [ ] **Step 1: Replace button classes**

```bash
cd /home/neovimmer/Documents/autimind
# .btn-sm-danger → .btn.btn-sm.btn-outline-danger
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/class="btn-sm btn-sm-danger"/class="btn btn-sm btn-outline-danger"/g' {} +
# .btn-sm-danger standalone  
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/class="btn-sm-danger"/class="btn btn-sm btn-outline-danger"/g' {} +
# .btn-sm-success
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/class="btn-sm btn-sm-success"/class="btn btn-sm btn-outline-success"/g' {} +
# .btn-sm btn-outline maintain as is (Bootstrap has .btn-outline-*)
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/class="btn-sm btn-outline"/class="btn btn-sm btn-outline-secondary"/g' {} +
# .btn-sm standalone
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/class="btn-sm"/class="btn btn-sm btn-outline-secondary"/g' {} +
# .btn-primary
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/class="btn-primary"/class="btn btn-primary"/g' {} +
# .btn-outline standalone
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/class="btn-outline"/class="btn btn-outline-secondary"/g' {} +
# .btn-danger
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/class="btn-danger"/class="btn btn-outline-danger"/g' {} +
# .btn (with no .btn- prefix variant)
# Already .btn, keep as-is
```

---

### Task 5: Bulk Convert Badges

- [ ] **Step 1: Replace badge classes**

```bash
cd /home/neovimmer/Documents/autimind
# .dash-badge-warning → .badge.bg-warning.text-dark
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/class="dash-badge dash-badge-warning"/class="badge bg-warning text-dark rounded-pill"/g' {} +
# .dash-badge-success → .badge.bg-success 
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/class="dash-badge dash-badge-success"/class="badge bg-success rounded-pill"/g' {} +
# .dash-badge-danger
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/class="dash-badge dash-badge-danger"/class="badge bg-danger rounded-pill"/g' {} +
# .dash-badge plain
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/class="dash-badge"/class="badge bg-primary rounded-pill"/g' {} +
```

---

### Task 6: Bulk Convert Grids

- [ ] **Step 1: Replace grid classes**

```bash
cd /home/neovimmer/Documents/autimind
# .dash-grid-2
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/class="dash-grid dash-grid-2"/class="row row-cols-1 row-cols-md-2 g-4"/g' {} +
# .dash-grid-2 standalone
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/class="dash-grid-2"/class="row row-cols-1 row-cols-md-2 g-4"/g' {} +
# .dash-grid-3
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/class="dash-grid-3"/class="row row-cols-1 row-cols-md-3 g-4"/g' {} +
# .dash-grid-4
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/class="dash-grid-4"/class="row row-cols-1 row-cols-md-4 g-4"/g' {} +
# .dash-grid-auto
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/class="dash-grid-auto"/class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4"/g' {} +
# .dash-grid standalone
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/class="dash-grid"/class="row row-cols-1 g-4"/g' {} +
```

---

### Task 7: Bulk Convert Forms

**Note:** Many form elements already work. Focus on label/input classes.

- [ ] **Step 1: Replace form element classes**

```bash
cd /home/neovimmer/Documents/autimind
# Add .form-control to text inputs, selects, textareas inside dash-form-like contexts
# This is complex because inputs have varying class attributes. Use grep to find targets.
# For inputs without existing classes inside form elements:
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/<input type="text"/<input type="text" class="form-control"/g' {} +
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/<input type="email"/<input type="email" class="form-control"/g' {} +
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/<input type="number"/<input type="number" class="form-control"/g' {} +
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/<input type="date"/<input type="date" class="form-control"/g' {} +
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/<input type="file"/<input type="file" class="form-control"/g' {} +
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/<input type="password"/<input type="password" class="form-control"/g' {} +
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/<input type="time"/<input type="time" class="form-control"/g' {} +
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/<select /<select class="form-select" /g' {} +
# Fix any double-class artifacts from inputs that already had a class
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/class="form-control" class="/class="/g' {} +
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/class="form-select" class="/class="/g' {} +
```

- [ ] **Step 2: Replace textarea classes**

```bash
cd /home/neovimmer/Documents/autimind
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/<textarea /<textarea class="form-control" /g' {} +
```

- [ ] **Step 3: Replace label classes inside forms**

```bash
cd /home/neovimmer/Documents/autimind
# Add .form-label to labels with 'for' attribute (most form labels)
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/<label for="/<label class="form-label" for="/g' {} +
# Fix any double-class artifacts
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/class="form-label" class="/class="/g' {} +
```

- [ ] **Step 4: Replace form structural classes**

```bash
cd /home/neovimmer/Documents/autimind
# .form-group → .mb-3 (Bootstrap form group equivalent)
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/class="form-group"/class="mb-3"/g' {} +
# .form-actions → .d-flex.gap-2
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/class="form-actions"/class="d-flex gap-2"/g' {} +
# .form-row → .row.g-3
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/class="form-row"/class="row g-3"/g' {} +
# .form-error → .invalid-feedback.d-block
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/class="form-error"/class="invalid-feedback d-block"/g' {} +
# .field-error → .invalid-feedback.d-block  
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/class="field-error"/class="invalid-feedback d-block"/g' {} +
# .field-error-border → .is-invalid
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/class="field-error-border"/class="is-invalid"/g' {} +
```

- [ ] **Step 5: Remove .dash-form class (Bootstrap forms need no wrapper class)**

```bash
cd /home/neovimmer/Documents/autimind
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/ class="dash-form"//g' {} +
```

---

### Task 8: Bulk Convert Headers + Layouts

- [ ] **Step 1: Replace .dash-header with Bootstrap flex layout**

```bash
cd /home/neovimmer/Documents/autimind
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/class="dash-header"/class="d-flex justify-content-between align-items-center mb-4"/g' {} +
```

- [ ] **Step 2: Replace .dash-empty-state**

```bash
cd /home/neovimmer/Documents/autimind
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/class="dash-empty-state"/class="card text-center p-5"/g' {} +
```

- [ ] **Step 3: Replace .dash-empty**

```bash
cd /home/neovimmer/Documents/autimind
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/class="dash-empty"/class="text-muted fst-italic"/g' {} +
```

- [ ] **Step 4: Replace .dash-link**

```bash
cd /home/neovimmer/Documents/autimind
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec sed -i 's/class="dash-link"/class="fw-semibold text-decoration-none"/g' {} +
```

---

### Task 9: Convert Pagination (admin/users.php)

- [ ] **Step 1: Replace pagination classes in users.php**

Apply manually (already done in previous session as `.dash-pagination` — now convert to Bootstrap pagination).

Replace the pagination section:
```diff
-<div class="dash-pagination">
+<nav><ul class="pagination justify-content-center mt-3">
   <?php if (($page ?? 1) > 1): ?>
-    <a href="..." class="btn-sm btn-outline">Previous</a>
+    <li class="page-item"><a class="page-link" href="...">Previous</a></li>
   <?php endif; ?>
   <?php for ($p = 1; $p <= ($totalPages ?? 1); $p++): ?>
-    <a href="..." class="dash-page-link <?= ($page ?? 1) === $p ? 'active' : '' ?>"><?= $p ?></a>
+    <li class="page-item <?= ($page ?? 1) === $p ? 'active' : '' ?>"><a class="page-link" href="..."><?= $p ?></a></li>
   <?php endfor; ?>
   <?php if (($page ?? 1) < ($totalPages ?? 1)): ?>
-    <a href="..." class="btn-sm btn-outline">Next</a>
+    <li class="page-item"><a class="page-link" href="...">Next</a></li>
   <?php endif; ?>
-</div>
+</ul></nav>
```

---

### Task 10: Convert .admin-welcome to Bootstrap

- [ ] **Step 1: Convert admin dashboard welcome section**

```bash
cd /home/neovimmer/Documents/autimind
sed -i 's/class="admin-welcome"/class="d-flex justify-content-between align-items-center mb-4"/g' app/Views/admin/dashboard.php
sed -i 's/class="admin-welcome-text"/class=""/g' app/Views/admin/dashboard.php
sed -i 's/class="admin-welcome-actions"/class="d-flex gap-2"/g' app/Views/admin/dashboard.php
```

---

### Task 11: Convert Stat Cards, Chart Cards, Specialist Cards

- [ ] **Step 1: Convert stat cards**

```bash
cd /home/neovimmer/Documents/autimind
sed -i 's/class="stat-cards"/class="row g-4"/g' app/Views/admin/dashboard.php
sed -i 's/class="stat-card-item"/class="card h-100 border-0 shadow-sm"/g' app/Views/admin/dashboard.php
sed -i 's/class="stat-card-body"/class="card-body"/g' app/Views/admin/dashboard.php
sed -i 's/class="stat-card-footer"/class="card-footer bg-transparent border-0"/g' app/Views/admin/dashboard.php
```

- [ ] **Step 2: Convert chart cards**

```bash
cd /home/neovimmer/Documents/autimind
sed -i 's/class="charts-section"/class="row g-4"/g' app/Views/admin/dashboard.php
sed -i 's/class="chart-card"/class="card"/g' app/Views/admin/dashboard.php
sed -i 's/class="chart-card-wide"/class="card col-12"/g' app/Views/admin/dashboard.php
sed -i 's/class="chart-card-header"/class="card-header"/g' app/Views/admin/dashboard.php
sed -i 's/class="chart-body"/class="card-body"/g' app/Views/admin/dashboard.php
```

- [ ] **Step 3: Convert specialist cards**

```bash
cd /home/neovimmer/Documents/autimind
sed -i 's/class="specialist-card"/class="card h-100"/g' app/Views/parent/specialists.php
```

---

### Task 12: Convert .dash-stat-card, .dash-stat-value, .dash-stat-label

- [ ] **Step 1: Replace stat display classes in progress-child**

```bash
cd /home/neovimmer/Documents/autimind
sed -i 's/class="dash-stat-card"/class="card text-center"/g' app/Views/admin/progress-child.php
sed -i 's/class="dash-stat-value"/class="h2 mb-0"/g' app/Views/admin/progress-child.php
sed -i 's/class="dash-stat-label"/class="text-muted small"/g' app/Views/admin/progress-child.php
```

---

### Task 13: Convert .table-responsive wrapping

- [ ] **Step 1: Ensure .table-responsive contains the table elements properly**

The current `.table-responsive` divs already wrap tables. This maps 1:1 with Bootstrap. No change needed for the wrapper itself.

---

### Task 14: Handle Modals

- [ ] **Step 1: Convert modal to Bootstrap modal**

In `app/Views/specialist/messages.php`:
```diff
-<div id="newMessageModal" class="modal" hidden>
+<div id="newMessageModal" class="modal fade" tabindex="-1" aria-hidden="true">
```
And update modal structure to use Bootstrap modal classes (`.modal-dialog`, `.modal-content`, `.modal-header`, `.modal-body`, `.modal-footer`).

---

### Task 15: Verify All Files Syntax

- [ ] **Step 1: PHP syntax check all modified files**

```bash
cd /home/neovimmer/Documents/autimind
find app/Views/admin app/Views/parent app/Views/specialist -name "*.php" -exec php -l {} \; 2>&1 | grep -v "No syntax errors"
```
Expected: No output (all pass)

- [ ] **Step 2: Verify no remaining .dash-table classes**

```bash
grep -rn 'class="dash-table"' app/Views/
```
Expected: No matches

- [ ] **Step 3: Verify no remaining .dash-card classes**

```bash
grep -rn 'class="dash-card"' app/Views/
```
Expected: No matches

- [ ] **Step 4: Verify no remaining .dash-badge classes**

```bash
grep -rn 'class="dash-badge"' app/Views/
```
Expected: No matches

---

### Task 16: Final Polish — Per-file Review

- [ ] **Step 1: Spot-check key files for layout issues**

Check `app/Views/admin/dashboard.php` — stat cards and chart cards should render in a grid.
Check `app/Views/admin/users.php` — pagination should show Bootstrap styling.
Check `app/Views/parent/dashboard.php` — cards in grid should render.
Check `app/Views/specialist/appointments.php` — confirm/complete/cancel buttons should show.
Check `app/Views/parent/specialists.php` — specialist cards should show.
