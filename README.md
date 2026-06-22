# AutiMind

AutiMind is a web application designed to support children with autism spectrum disorder and their families. It provides interactive games, sensory-friendly activities, screening tools, AI-powered chat assistance, and connects families with specialists.

## Features

- **AI Chatbot** — Powered by OpenRouter AI for intelligent, compassionate responses about autism support
- **Interactive Games** — Skill-building exercises for communication, sensory regulation, and problem-solving
- **Screening Quiz** — Developmental screening tool for early autism signs
- **Progress Tracking** — Monitor developmental milestones and activity completion
- **Specialist Directory** — Browse and book appointments with certified professionals
- **Parent Dashboard** — Manage children, view progress, and communicate with specialists
- **Admin Panel** — Manage users, content, chatbot responses, and OpenRouter configuration

## Requirements

- PHP 8.1 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Composer
- cURL extension enabled
- A web server (Apache/Nginx) — Laragon includes this

## Installation Guide for Laragon (Windows)

### Step 1: Install Laragon

1. Download Laragon from [https://laragon.org/download/](https://laragon.org/download/)
2. Run the installer (Laragon Full edition recommended — includes Apache, MySQL, PHP, Composer, Git)
3. Launch Laragon. Make sure the tray icon shows green (if not, click **Start All**)

### Step 2: Enable Required PHP Extensions

1. In Laragon, go to **Menu → PHP → Settings**
2. Enable (check) these extensions:
   - `curl`
   - `pdo_mysql`
   - `mbstring`
   - `openssl`
   - `fileinfo`
3. Click **OK** and then **Menu → Apache → Restart**

### Step 3: Clone the Project

1. Open Laragon's terminal: **Menu → Terminal**
2. Navigate to Laragon's www directory:
   ```bash
   cd ~/Laragon/www
   ```
3. Clone the repository:
   ```bash
   git clone https://github.com/your-username/autimind.git
   ```
4. The project will be accessible at `http://autimind.test` (Laragon auto-creates this)

### Step 4: Install Dependencies

In the Laragon terminal, run:

```bash
cd autimind
composer install
```

### Step 5: Configure Environment

1. Copy the example environment file:
   ```bash
   cp .env.example .env
   ```
2. Open `.env` in a text editor and update:
   ```env
   APP_URL=http://autimind.test
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_NAME=autimind
   DB_USER=root
   DB_PASS=
   ```

   Leave DB_PASS empty if using Laragon's default MySQL (no password).

### Step 6: Create the Database

1. In Laragon, click **Database** to open HeidiSQL (or use **Menu → MySQL → MySQL Console**)
2. Run:
   ```sql
   CREATE DATABASE IF NOT EXISTS autimind CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. Import the schema:
   - In HeidiSQL, select the `autimind` database
   - Go to **File → Load SQL File** → select `migrations/001_create_tables.sql`
   - Execute (or press F9)

### Step 7: Configure OpenRouter AI (Optional but Recommended)

1. Sign up for a free account at [https://openrouter.ai/](https://openrouter.ai/)
2. Generate an API key from your dashboard
3. Edit `.env` and add:
   ```env
   OPENROUTER_API_KEY=sk-or-v1-your-api-key-here
   OPENROUTER_MODEL=google/gemma-4-31b-it:free
   ```
4. You can also change the model later from the Admin Panel → Chatbot page

### Step 8: Set Folder Permissions

Laragon on Windows usually handles this, but ensure these folders are writable:

```bash
chmod -R 755 public/uploads
```

(On Windows, right-click the `public/uploads` folder → Properties → Security → make sure your user has full control)

### Step 9: Access the Application

1. In Laragon, click **Start All** if not already running
2. Open your browser and go to: `http://autimind.test`
3. Register a new account or log in
4. To access the admin panel, set the first user's role to `admin` in the database:
   ```sql
   UPDATE users SET role = 'admin' WHERE email = 'your-email@example.com';
   ```

## Configuration

### OpenRouter AI

The chatbot uses OpenRouter AI to provide intelligent responses. You can:

- **Set the API key**: Edit `OPENROUTER_API_KEY` in `.env`
- **Change the model**: Either edit `OPENROUTER_MODEL` in `.env` or use the Admin Panel → Chatbot page

Free models available on OpenRouter:
- `google/gemma-4-31b-it:free` (recommended)
- `microsoft/phi-3-mini-128k-instruct:free`
- `mistralai/mistral-7b-instruct:free`

### Keyword Responses (Fallback)

The system includes a keyword-based response system as fallback when OpenRouter is not configured. Manage responses from the Admin Panel → Chatbot page.

## Project Structure

```
autimind/
├── app/
│   ├── Controllers/       # Application controllers
│   ├── Core/              # Core framework (Router, View, Database, etc.)
│   ├── Models/            # Database models
│   ├── Services/          # Business logic services
│   └── Views/             # View templates
│       ├── admin/         # Admin dashboard views
│       ├── auth/          # Login/registration views
│       ├── layouts/       # Layout templates
│       ├── partials/      # Reusable partials (nav, footer)
│       ├── parent/        # Parent dashboard views
│       ├── public/        # Public pages
│       └── specialist/    # Specialist dashboard views
├── migrations/            # Database migration files
├── public/                # Public entry point + assets
│   └── assets/
│       ├── css/           # Stylesheets
│       └── js/            # JavaScript files
├── routes/
│   └── web.php            # Route definitions
├── .env                   # Environment configuration
└── README.md
```
