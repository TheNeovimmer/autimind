# AutiMind — Autism Support Platform

A full-stack PHP MySQL MVC platform for autism screening, progress tracking, specialist booking, and community support. Built with PHP (no framework), MySQL, and a responsive dark-themed UI.

## Roles & Credentials

| Role | Email | Password |
|------|-------|----------|
| **Admin** | admin@autimind.com | admin123 |
| **Specialist (Dr. Sarah Chen)** | sarah@autimind.com | admin123 |
| **Specialist (David Okonkwo)** | david@autimind.com | admin123 |

Sign up as a **Parent** at `/signup` to create a parent account.

## Features

### Public
- 17 static-style pages (home, about, services, FAQ, contact, etc.)
- Specialist directory with profile cards
- Contact form
- Newsletter subscription

### Parent Dashboard (`/parent/*`)
- Multi-child management (add, edit, delete)
- Clinical screening quiz (10 questions, 4 categories, weighted scoring 0–5)
- Risk assessment: low (≤15), moderate (16–30), high (31–50)
- Per-category breakdown + score history
- Progress tracking & insights
- Browse & book specialists
- Appointment management (book, cancel)
- Messaging with specialists
- AI chatbot (keyword-match + fallback, history persisted)
- Profile settings with avatar upload

### Specialist Dashboard (`/specialist/*`)
- Patient list with profiles & quiz history
- Appointment management (confirm, cancel)
- Messaging with parents
- Schedule management
- Profile settings with avatar upload

### Admin Dashboard (`/admin/*`)
- **Users** — full CRUD
- **Specialists** — manage specialist profiles
- **Quiz** — create/edit questions, options, weights
- **Activities** — create/edit therapeutic activities
- **Appointments** — view all
- **Messages** — view all conversations
- **Subscriptions** — full CRUD
- **Contacts** — view contact form submissions
- **FAQ** — full CRUD
- **Settings** — profile with avatar upload

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 8.2+ (no framework) |
| Architecture | Flat MVC + Service Layer |
| Autoloading | Composer (PSR-4) |
| Database | MySQL / MariaDB |
| Database Access | PDO with prepared statements |
| Auth | Session-based with role middleware |
| Frontend | HTML, CSS, vanilla JS |
| Fonts | Geist (headings), Inter (body) |
| Icons | Font Awesome 6 |
| Smooth Scroll | Lenis |

## Project Structure

```
├── app/
│   ├── Controllers/       # AuthController, HomeController, ParentController,
│   │                       # SpecialistController, AdminController
│   ├── Core/              # App, Database, Router, Session, Validator, View, Migration
│   ├── Models/            # User, Child, QuizQuestion, QuizAttempt, Appointment,
│   │                       # Message, Activity, Subscription
│   ├── Services/          # QuizScoringService, ChatbotService, InsightService
│   └── Views/
│       ├── layouts/       # main.php (public), dashboard.php (auth)
│       ├── partials/      # nav, footer, dashboard-sidebar, dashboard-topbar
│       ├── auth/          # login, signup, forgot-password, reset-password
│       ├── parent/        # 16 views
│       ├── specialist/    # 8 views
│       ├── admin/         # 21 views
│       └── public/        # 17 public pages
├── migrations/            # 001_create_tables.sql, 002_seed_data.sql
├── public/
│   ├── assets/css/        # styles.css, dashboard.css
│   ├── assets/js/         # app.js
│   └── uploads/avatars/   # Profile picture uploads
├── routes/
│   └── web.php            # 160+ routes
├── .env                   # Database configuration
├── composer.json
└── README.md
```

## Setup

### Requirements
- PHP 8.2+
- MySQL / MariaDB
- Composer
- Apache/Nginx with `mod_rewrite`
- `ext-pdo`, `ext-mbstring`, `ext-fileinfo`

### Quick Start (DDEV)

```bash
ddev start
ddev composer install
# Migrate + seed:
ddev mysql autimind < migrations/001_create_tables.sql
ddev mysql autimind < migrations/002_seed_data.sql
# Configure .env:
# DB_HOST=db, DB_USER=root, DB_PASS=root
```

### Manual Setup

```bash
git clone <repo>
cd autimind
composer install
cp .env.example .env
# Edit .env with your database credentials
mysql -u root -p autimind < migrations/001_create_tables.sql
mysql -u root -p autimind < migrations/002_seed_data.sql
# Point your web root to public/
```

## Routes

| Area | Base Path | Auth Required |
|------|-----------|---------------|
| Public | `/` | No |
| Auth | `/login`, `/signup`, `/forgot-password`, `/reset-password` | No |
| Parent | `/parent/*` | Yes (role: parent) |
| Specialist | `/specialist/*` | Yes (role: specialist) |
| Admin | `/admin/*` | Yes (role: admin) |

## Database

The database schema includes 12 tables covering users, children, quiz (questions, options, attempts, answers), appointments, messages, chat history, activities, subscriptions, specialist details, FAQ, contacts, and password resets.
