# AutiMind — PHP MVC Enterprise Conversion Design

## Overview

Convert the existing 14-page static HTML autism support platform into a full-stack PHP MVC application with MySQL, role-based access control (Parent / Specialist / Admin), per-role dashboards, and enterprise-grade CRUD systems for all entities.

## Tech Stack

- **Backend**: PHP 8.x (native, no framework)
- **Database**: MySQL with PDO
- **Frontend**: Existing HTML/CSS/JS design preserved, converted to PHP views
- **Auth**: Session-based with middleware role gate
- **Autoloading**: PSR-4 via Composer or custom spl_autoload_register
- **Environment**: `.env` file for configuration

## Architecture Approach

Flat MVC with Service Layer (Approach 1):

```
autimind/
├── public/
│   ├── index.php              (front controller)
│   ├── .htaccess              (URL rewriting)
│   └── assets/                (existing CSS, JS, images)
├── app/
│   ├── Core/
│   │   ├── Router.php         (regex-based route matching)
│   │   ├── Database.php       (PDO singleton)
│   │   ├── Session.php        (session wrapper)
│   │   ├── Validator.php      (form validation)
│   │   ├── View.php           (layout rendering with content slot)
│   │   └── Middleware.php     (auth + role gates)
│   ├── Controllers/           (one per domain)
│   ├── Models/                (thin PDO wrappers per table)
│   ├── Services/              (business logic: scoring, chatbot, insights)
│   ├── Views/
│   │   ├── layouts/           (main.php, dashboard.php)
│   │   ├── auth/              (login, signup, forgot-password, reset)
│   │   ├── public/            (index, about, pricing, contact, faq, etc.)
│   │   ├── parent/            (parent dashboard pages)
│   │   ├── specialist/        (specialist dashboard pages)
│   │   └── admin/             (admin dashboard pages)
│   └── Config/                (app.php, database.php)
├── migrations/                (SQL migration files)
└── vendor/                    (Composer autoloader)
```

## Database Schema

### users
| Column | Type | Notes |
|--------|------|-------|
| id | INT PK AUTO_INCREMENT | |
| role | ENUM('parent','specialist','admin') | |
| name | VARCHAR(255) | |
| email | VARCHAR(255) UNIQUE | |
| password | VARCHAR(255) | password_hash |
| avatar | VARCHAR(255) NULL | |
| phone | VARCHAR(50) NULL | |
| is_active | TINYINT(1) DEFAULT 1 | |
| created_at | DATETIME | |
| updated_at | DATETIME | |

### children
| Column | Type | Notes |
|--------|------|-------|
| id | INT PK AUTO_INCREMENT | |
| parent_id | INT FK → users.id | |
| name | VARCHAR(255) | |
| age | INT NULL | |
| avatar | VARCHAR(255) NULL | |
| birth_date | DATE NULL | |
| diagnosis_status | VARCHAR(100) NULL | |
| notes | TEXT NULL | |
| created_at | DATETIME | |
| updated_at | DATETIME | |

### specialist_details
| Column | Type | Notes |
|--------|------|-------|
| id | INT PK AUTO_INCREMENT | |
| user_id | INT FK → users.id UNIQUE | |
| title | VARCHAR(255) | |
| bio | TEXT | |
| specializations | TEXT | JSON array |
| years_experience | INT | |
| is_available | TINYINT(1) DEFAULT 1 | |
| created_at | DATETIME | |
| updated_at | DATETIME | |

### quiz_questions
| Column | Type | Notes |
|--------|------|-------|
| id | INT PK AUTO_INCREMENT | |
| question_text | TEXT | |
| category | ENUM('social_communication','behavior','sensory','developmental') | |
| order_index | INT | |
| is_active | TINYINT(1) DEFAULT 1 | |
| created_at | DATETIME | |

### quiz_options
| Column | Type | Notes |
|--------|------|-------|
| id | INT PK AUTO_INCREMENT | |
| question_id | INT FK → quiz_questions.id | |
| option_text | VARCHAR(255) | |
| weight | INT | Scoring weight (0-5) |
| order_index | INT | |

### quiz_attempts
| Column | Type | Notes |
|--------|------|-------|
| id | INT PK AUTO_INCREMENT | |
| child_id | INT FK → children.id | |
| started_at | DATETIME | |
| completed_at | DATETIME NULL | |
| total_score | INT NULL | |
| risk_level | ENUM('low','moderate','high') NULL | |
| status | ENUM('in_progress','completed') DEFAULT 'in_progress' | |

### quiz_answers
| Column | Type | Notes |
|--------|------|-------|
| id | INT PK AUTO_INCREMENT | |
| attempt_id | INT FK → quiz_attempts.id | |
| question_id | INT FK → quiz_questions.id | |
| option_id | INT FK → quiz_options.id | |

### appointments
| Column | Type | Notes |
|--------|------|-------|
| id | INT PK AUTO_INCREMENT | |
| child_id | INT FK → children.id | |
| specialist_id | INT FK → users.id | |
| parent_id | INT FK → users.id | |
| date | DATE | |
| time | TIME | |
| duration | INT | Minutes |
| status | ENUM('pending','confirmed','cancelled','completed') DEFAULT 'pending' | |
| notes | TEXT NULL | |
| created_at | DATETIME | |

### messages
| Column | Type | Notes |
|--------|------|-------|
| id | INT PK AUTO_INCREMENT | |
| sender_id | INT FK → users.id | |
| receiver_id | INT FK → users.id | |
| subject | VARCHAR(255) | |
| body | TEXT | |
| is_read | TINYINT(1) DEFAULT 0 | |
| created_at | DATETIME | |

### activities
| Column | Type | Notes |
|--------|------|-------|
| id | INT PK AUTO_INCREMENT | |
| title | VARCHAR(255) | |
| description | TEXT | |
| category | ENUM('games','puzzles','stories','video','coloring') | |
| difficulty | ENUM('easy','medium','hard') | |
| image_url | VARCHAR(255) NULL | |
| is_active | TINYINT(1) DEFAULT 1 | |
| created_at | DATETIME | |

### child_progress
| Column | Type | Notes |
|--------|------|-------|
| id | INT PK AUTO_INCREMENT | |
| child_id | INT FK → children.id | |
| activity_id | INT FK → activities.id | |
| score | INT NULL | |
| completed_at | DATETIME | |
| time_spent_seconds | INT NULL | |

### subscriptions
| Column | Type | Notes |
|--------|------|-------|
| id | INT PK AUTO_INCREMENT | |
| user_id | INT FK → users.id | |
| plan | ENUM('standard','premium','family') | |
| status | ENUM('active','cancelled','expired') DEFAULT 'active' | |
| started_at | DATETIME | |
| ends_at | DATETIME NULL | |

### contact_messages
| Column | Type | Notes |
|--------|------|-------|
| id | INT PK AUTO_INCREMENT | |
| name | VARCHAR(255) | |
| email | VARCHAR(255) | |
| subject | VARCHAR(255) | |
| message | TEXT | |
| is_read | TINYINT(1) DEFAULT 0 | |
| created_at | DATETIME | |

### chatbot_responses
| Column | Type | Notes |
|--------|------|-------|
| id | INT PK AUTO_INCREMENT | |
| keywords | TEXT | JSON array of trigger keywords |
| response_text | TEXT | |
| category | VARCHAR(100) NULL | |
| is_active | TINYINT(1) DEFAULT 1 | |

### chat_history
| Column | Type | Notes |
|--------|------|-------|
| id | INT PK AUTO_INCREMENT | |
| user_id | INT FK → users.id | |
| message | TEXT | |
| sender | ENUM('user','bot') | |
| created_at | DATETIME | |

### password_resets
| Column | Type | Notes |
|--------|------|-------|
| id | INT PK AUTO_INCREMENT | |
| email | VARCHAR(255) | |
| token | VARCHAR(255) | |
| expires_at | DATETIME | |
| used | TINYINT(1) DEFAULT 0 | |
| created_at | DATETIME | |

### faq_items
| Column | Type | Notes |
|--------|------|-------|
| id | INT PK AUTO_INCREMENT | |
| question | TEXT | |
| answer | TEXT | |
| category | ENUM('general','features','pricing','technical') | |
| order_index | INT | |
| is_active | TINYINT(1) DEFAULT 1 | |
| created_at | DATETIME | |

## Routing System

Simple regex-based router in `Router.php`:
- `Route::get($uri, $controllerAction, $middleware)` for GET
- `Route::post($uri, $controllerAction, $middleware)` for POST
- Middleware: `'auth'` (requires login), `'role:parent'` (requires specific role)
- Named parameters: `/parent/children/{id}` → `$params['id']`

## Authentication & Authorization

- Session-based with `Session.php` wrapper
- Login: verify email/password via `password_verify()`, set session vars
- Signup: validate, hash password, insert user, auto-login
- Middleware chain: auth check → role check → redirect if unauthorized
- Password reset: token in `password_resets` table, expiry 1 hour
- Redirect after login: based on `$_SESSION['role']`

## View System

- `View::render('path/to/view', $data)` loads `app/Views/path/to/view.php`
- Layouts: `layouts/main.php` (public pages) and `layouts/dashboard.php` (auth pages)
- Layout receives `$content` variable from the rendered view
- Partials: `partial('nav', $data)` loads `app/Views/partials/nav.php`
- All existing HTML/CSS/JS preserved, PHP only replaces static content with dynamic data

## Parent Dashboard (route prefix: /parent)

Pages: dashboard, children (list + CRUD), quiz (start + results), progress, specialists (browse + book), appointments (list + CRUD), messages (inbox + thread), chatbot, settings

Key features:
- Multi-child selector at top of dashboard
- Quiz scoring: weighted answers → total score → risk level (low/moderate/high) with clinical-style mapping
- AI Insights: generated from child's activity and quiz data via InsightService
- Booking: select specialist, pick available slot, confirm appointment

## Specialist Dashboard (route prefix: /specialist)

Pages: dashboard, patients (list + detail), appointments (calendar/list), messages, schedule (availability), settings

## Admin Dashboard (route prefix: /admin)

Pages: dashboard (stats), users (CRUD), specialists (approve), quiz (CRUD), activities (CRUD), appointments, messages (read-only), subscriptions (CRUD + manual assign), contacts, faq (CRUD), settings

## Quiz Scoring Algorithm (QuizScoringService)

- 10 questions across 4 categories: social_communication (3), behavior (3), sensory (2), developmental (2)
- Each option has a weight (0-5): 0 = never/never, 5 = always/always
- Total score range: 0-50
- Risk mapping: 0-15 = low, 16-30 = moderate, 31-50 = high
- Category breakdown: per-category score and percentage
- Historical tracking: multiple attempts over time shown in results chart

## Chatbot Service (ChatbotService)

- Keyword-based matching against `chatbot_responses` table
- User input → tokenize → match keywords → return response text
- Fallback: "I'm not sure I understand. Could you rephrase that?"
- All conversations stored in `chat_history`

## Insight Service (InsightService)

- Generates 2 personalized insight cards for the parent dashboard
- **Strength**: based on child's highest-scoring activity category
- **Recommendation**: based on lowest quiz category score
- Content drawn from predefined templates mapped to categories/levels

## API Endpoints (JSON responses for AJAX features)

- `GET /api/quiz/questions` — Returns all active questions with options
- `POST /api/quiz/submit` — Submits answers, returns score + risk level
- `POST /api/chatbot/message` — Sends user message, returns bot response
- `GET /api/children/{id}/progress` — Returns progress data for charts

## File Organization for Views

Each dashboard page is a small PHP file that:
1. Receives data from the controller
2. Outputs HTML by echoing PHP variables in the existing markup pattern
3. Uses `htmlspecialchars()` for all user-generated content
4. Keeps the existing CSS classes and structure intact

## Error Handling

- 404: Router throws `NotFoundHttpException` → renders `Views/errors/404.php`
- 403: Middleware throws `ForbiddenException` → renders `Views/errors/403.php`
- 500: Global try/catch in `index.php` → renders `Views/errors/500.php`
- Form validation errors returned to view via `$errors` array
- Session flash messages for success/error feedback

## Security Measures

- All passwords hashed with `password_hash(PASSWORD_BCRYPT)`
- `htmlspecialchars()` on all output
- Prepared statements via PDO for all queries
- CSRF token on all POST forms
- Session regeneration after login
- Middleware prevents unauthorized access at route level
- `.env` file excluded from git (`.env.example` committed instead)

## Implementation Order

1. **Core framework** — Router, Database, Session, View, Middleware, autoloader
2. **Auth system** — Login, signup, logout, password reset, role middleware
3. **Database migrations** — All tables
4. **Public pages** — Home, About, Pricing, Contact, FAQ (now dynamic)
5. **Parent dashboard** — All pages and features
6. **Specialist dashboard** — All pages
7. **Admin dashboard** — All CRUD management pages
8. **API endpoints** — Quiz, chatbot, progress (AJAX)
9. **Polish & testing** — Error pages, validation, edge cases
