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
2. Run the installer (Laragon Full edition recommended — includes Apache, MySQL, PHP, Composer, Git, phpMyAdmin)
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

### Step 3: Download the Project

**Option A — Download ZIP (recommended):**

1. Go to [https://github.com/TheNeovimmer/autimind](https://github.com/TheNeovimmer/autimind)
2. Click the green **Code** button, then select **Download ZIP**
3. Extract the ZIP file
4. Copy the extracted `autimind` folder into Laragon's `www` directory (usually `C:\laragon\www\`)
5. The project will be accessible at `http://autimind.test` (Laragon auto-creates this)

**Option B — Clone with Git:**

1. Open Laragon's terminal: **Menu → Terminal**
2. Navigate to Laragon's www directory:
   ```bash
   cd ~/Laragon/www
   ```
3. Clone the repository:
   ```bash
   git clone https://github.com/TheNeovimmer/autimind.git
   ```

### Step 4: Install Dependencies

Laragon includes Composer. Open Laragon's terminal (**Menu → Terminal**) and run:

```bash
cd autimind
composer install
```

If Composer is not found, download it from [https://getcomposer.org/download/](https://getcomposer.org/download/) and install it.

### Step 5: Configure Environment

1. In the `autimind` folder, locate `.env.example` and rename it to `.env`
2. Right-click `.env` → **Edit with notepad** (or any text editor)
3. Update these values:
   ```env
   APP_URL=http://autimind.test
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_NAME=autimind
   DB_USER=root
   DB_PASS=
   ```
   Leave DB_PASS empty — Laragon's default MySQL has no password.

### Step 6: Create the Database & Import Schema

1. In Laragon, click **Start All** (Apache + MySQL must be running)
2. Click **Menu → Database** — this opens phpMyAdmin in your browser
3. In phpMyAdmin:
   - Click the **Databases** tab at the top
   - Under **Create database**, enter `autimind`
   - Select `utf8mb4_general_ci` as the collation
   - Click **Create**
4. Import the SQL files (one at a time):
   - Click on the `autimind` database in the left sidebar
   - Click the **Import** tab at the top of the page
   - Click the **Choose File** button under "File to import"
   - Browse and select `migrations/001_create_tables.sql`
   - Scroll to the bottom and click **Import**
   - Wait for the green success message
5. Repeat for the second file:
   - Click the **Import** tab again
   - Click **Choose File** and select `migrations/002_seed_data.sql`
   - Click **Import** at the bottom
6. You should see: "Import has been successfully finished" for both files

### Step 7: Default Accounts

After importing `002_seed_data.sql`, these accounts are available:

| Role | Email | Password |
|------|-------|----------|
| **Admin** | admin@autimind.com | admin123 |
| **Specialist** | sarah@autimind.com | admin123 |
| **Specialist** | david@autimind.com | admin123 |

Login at `http://autimind.test/login` using the admin account to access the admin panel.

### Step 8: Configure OpenRouter AI (Optional but Recommended)

1. Sign up for a free account at [https://openrouter.ai/](https://openrouter.ai/)
2. Generate an API key from your dashboard
3. Edit `.env` and add:
   ```env
   OPENROUTER_API_KEY=sk-or-v1-your-api-key-here
   OPENROUTER_MODEL=google/gemma-4-31b-it:free
   ```
4. You can also change the model later from the Admin Panel → Chatbot page

### Step 9: Set Folder Permissions

On Windows, ensure the uploads folder is writable:

1. Navigate to `C:\laragon\www\autimind\public\uploads`
2. Right-click the `uploads` folder → **Properties**
3. Go to the **Security** tab
4. Select your user and make sure **Full control** is checked
5. Click **Apply** → **OK**

### Step 10: Access the Application

1. In Laragon, click **Start All** if not already running
2. Open your browser and go to: `http://autimind.test`
3. Register a new account or log in
4. To access the admin panel, set the first user's role to `admin` in the database:
   - Open phpMyAdmin (Laragon → Menu → Database)
   - Click the `autimind` database
   - Click the **SQL** tab
   - Run:
     ```sql
     UPDATE users SET role = 'admin' WHERE email = 'your-email@example.com';
     ```
   - Click **Go**

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
