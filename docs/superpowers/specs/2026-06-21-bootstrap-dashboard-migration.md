# Bootstrap Dashboard Migration

**Date:** 2026-06-21
**Status:** Approved Design

## Goal

Convert all dashboard UI (parent/specialist/admin roles, ~60+ PHP view files) from pure custom CSS to Bootstrap 5.3. Keep the same visual identity (purple brand, Inter typography). Enhance UI as part of the conversion. Public frontend site stays unchanged.

## Scope

- **In scope:** `app/Views/layouts/dashboard.php`, `app/Views/partials/dashboard-topbar.php`, `app/Views/parent/*`, `app/Views/specialist/*`, `app/Views/admin/*`, `public/assets/css/dashboard.css`
- **Out of scope:** `app/Views/public/*`, `app/Views/layouts/main.php`, `app/Views/auth/*`, `public/assets/css/styles.css`

## Delivery

Bootstrap 5.3.3 loaded via CDN. No build step. No npm.

## Architecture

### CSS Strategy

`public/assets/css/dashboard.css` is refactored into two layers:

1. **Bootstrap brand overrides** — set CSS variables for `--bs-primary`, `--bs-body-font-family`, etc. Bootstrap components inherit these.
2. **Custom components** — only for UI that Bootstrap does not provide: sidebar, topbar layout, stat-card icons, chat bubbles, specialist cards, quiz components, calendar grid, pricing cards, child list compact, appointment list compact, insight cards, animations.

### Component Mapping

| Custom Class | Bootstrap Replacement |
|---|---|
| `.dash-card` | `.card.card-dash` |
| `.dash-card-header` | `.card-header` |
| `.dash-card-footer` | `.card-footer` |
| Content in cards | `.card-body` |
| `.dash-table` | `.table.table-striped.table-hover.align-middle.mb-0` |
| `.table-responsive` | `.table-responsive` (same) |
| `.dash-form` | plain form |
| `.form-group` | `.mb-3` |
| `label` inside forms | `.form-label` |
| `input`, `select`, `textarea` | `.form-control` |
| `.form-error` | `.invalid-feedback` + `.is-invalid` |
| `.form-actions` | `.d-flex.gap-2` |
| `.form-row` | `.row.g-3` |
| `.btn` | `.btn` (Bootstrap) |
| `.btn-primary` | `.btn-primary` (brand via CSS vars) |
| `.btn-outline` | `.btn-outline-secondary` |
| `.btn-danger` | `.btn-outline-danger` |
| `.btn-sm-danger` | `.btn.btn-sm.btn-outline-danger` |
| `.btn-sm-success` | `.btn.btn-sm.btn-outline-success` |
| `.dash-badge` | `.badge.bg-primary.rounded-pill` |
| `.dash-badge-warning` | `.badge.bg-warning.text-dark` |
| `.dash-badge-success` | `.badge.bg-success` |
| `.dash-badge-danger` | `.badge.bg-danger` |
| `.dash-link` | `.fw-semibold` + brand color |
| `.dash-pagination` | `.pagination.justify-content-center.mt-3` |
| Page links | `.page-item` + `.page-link` |
| `.modal` | Bootstrap modal (same class, uses Bootstrap JS) |
| `.dash-grid-2/3/4` | `.row.row-cols-1.row-cols-md-2/3/4.g-4` |
| `.dash-grid-auto` | `.row.row-cols-1.row-cols-sm-2.row-cols-md-3.g-4` |
| `.dash-empty-state` | `.card.text-center.p-5` |
| `.dash-empty` | `.text-muted.fst-italic` |
| `.d-flex` / `.gap-*` / `.mb-*` / `.text-muted` / `.text-center` | Same (Bootstrap matches) |
| `.d-inline` / `.flex-1` / `.flex-wrap` | Same (Bootstrap matches) |
| `.dash-header` | `.d-flex.justify-content-between.align-items-center.mb-4` |
| `.admin-welcome` | `.d-flex.justify-content-between.align-items-center.mb-4` |
| `.stat-cards` | `.row.g-4` |
| `.stat-card-item` | `.card.h-100.border-0.shadow-sm` |
| `.charts-section` | `.row.g-4` |
| `.chart-card` | `.card` |
| `.dashboard-content` | `.container-fluid.p-4` |

### Custom CSS Kept (no Bootstrap equivalent)

- Sidebar: `.dash-sidebar`, `.dash-sidebar-logo`, `.dash-sidebar-nav`, `.dash-nav-item`, `.dash-sidebar-footer`
- Topbar layout: `.dash-topbar`, `.dash-topbar-left`, `.dash-topbar-right`, `.dash-user-avatar`, `.dash-user-name`
- Stat card icons: `.stat-card-icon` (icon circles with per-type colors)
- Chat: `.chat-container`, `.chat-messages`, `.chat-message`, `.chat-bubble`, `.chatbot-input`
- Specialist grid: `.specialist-grid`, `.specialist-card`, `.specialist-tags`, `.tag`
- Quiz: `.quiz-question`, `.quiz-option`, `.result-summary`, `.score-bar-fill`, `.category-breakdown`
- Calendar: `.calendar-grid`, `.calendar-cell`, `.calendar-nav`
- Pricing: `.pricing-card`, `.pricing-price`, `.pricing-features`, `.current-plan-badge`
- Child list compact, appointment list compact, message list, insight cards
- Animations: `@keyframes fadeInUp`
- Status/risk/role badge colors
- Avatar upload wrapper
- Brand CSS variables

## Implementation Order

### Phase 1 — Layout (2 files)
1. `app/Views/layouts/dashboard.php` — add Bootstrap CDN links, add `.container-fluid.p-4` to dashboard-content
2. `app/Views/partials/dashboard-topbar.php` — convert to Bootstrap `.navbar` + `.dropdown`
3. `public/assets/css/dashboard.css` — refactor: remove all Bootstrap-covered component CSS, set Bootstrap CSS variables for brand, keep custom components

### Phase 2 — Bulk Pattern Replacements (60+ files)
Process by pattern across all view files:

| Order | Pattern | Files Affected |
|-------|---------|---------------|
| 1 | Tables (`.dash-table` → `.table.*`) | 15+ files |
| 2 | Cards (`.dash-card` → `.card`, `.dash-card-header` → `.card-header`) | 30+ files |
| 3 | Buttons (`.btn-sm-danger` → `.btn-outline-danger-sm`, etc.) | 15+ files |
| 4 | Badges (`.dash-badge` → `.badge`) | 8 files |
| 5 | Forms (`label` → `.form-label`, `input` → `.form-control`) | 12 files |
| 6 | Grids (`.dash-grid-*` → `.row-cols-*`) | 10 files |
| 7 | Empty states | 10 files |
| 8 | Pagination | 1 file (admin/users.php) |
| 9 | `.dash-header` → flexbox layout | 20 files |

### Phase 3 — Polish
- Verify all custom CSS still works with Bootstrap loaded
- Check no class conflicts
- Test all 3 role dashboards
- Remove dead CSS after migration

## Verification

- PHP syntax check on all modified view files
- Visual check of parent/specialist/admin dashboards
- Confirm sidebar, topbar, chat, quiz, calendar, specialist grid render correctly
- Confirm modals work with Bootstrap JS
- Confirm no broken layouts due to Bootstrap vs custom CSS conflicts
