# CBT (Computer-Based Testing) Exam System

A modern, robust, and secure Computer-Based Testing (CBT) platform built with **Laravel 12 (PHP 8.4)**, **Filament v4**, and **Livewire 3**.

---

## 🚀 Key Features

### 👨‍🎓 Student Portal (Livewire 3)
* **Interactive Exam Room**: Real-time timer countdown, question palette navigator, and question bookmarking (ragu-ragu).
* **Auto-Save & State Recovery**: Answers are automatically persisted in real-time. Students can resume smoothly if disconnected.
* **Proctoring & Anti-Cheat**: Tab-switch / blur detection with tolerance warnings and automatic submission on violation.
* **Instant Results & Discussion**: Detailed score calculation, pass/fail indicators, and full review of questions with answer keys and explanations.

### 🛡️ Admin & Proctor Panel (Filament v4)
* **Exam Management**: Create exams, configure duration, passing scores, question & option randomization, max attempts, and scheduling.
* **Question Bank**: Rich question builder with multiple-choice options, correct answer indicators, scoring points, and answer explanations.
* **Live Attempt Monitoring**: Track active student attempts in real-time, view detailed student responses, and reset attempts when necessary.
* **Role-Based Access Control**: Powered by Spatie Permission (`admin`, `proctor`, `teacher`, `student`).

---

## 💻 Quick Start Guide

You can run this application either **natively (fastest, no Docker required)** or with **Docker Compose**.

### Option 1: Running Locally with SQLite (Recommended for Local Dev)

#### Prerequisites
* PHP >= 8.4 (with `pdo_sqlite` extension enabled)
* Composer
* Node.js & NPM

#### Installation Steps
1. **Clone the repository:**
   ```bash
   git clone https://github.com/<your-username>/<your-repo-name>.git
   cd <your-repo-name>
   ```

2. **Install dependencies:**
   ```bash
   composer install
   npm install && npm run build
   ```

3. **Configure environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Initialize database & seed data:**
   ```bash
   # Create SQLite database file
   touch database/database.sqlite
   
   # Run migrations and seed default users & sample exam
   php artisan migrate --seed
   ```

5. **Start the local server:**
   ```bash
   php artisan serve --port=8080
   ```
   Access the app at:
   * **Student Portal:** [http://localhost:8080](http://localhost:8080)
   * **Admin Panel:** [http://localhost:8080/admin](http://localhost:8080/admin)

---

### Option 2: Running with Docker Compose

If you prefer full containerization with PostgreSQL and Redis:

1. **Start the containers:**
   ```bash
   docker compose up -d --build
   ```

2. **Run migrations and seeder inside the app container:**
   ```bash
   docker compose exec app php artisan migrate:fresh --seed --force
   ```

3. **Available services:**
   * **Web Application:** [http://localhost:8080](http://localhost:8080)
   * **Filament Admin:** [http://localhost:8080/admin](http://localhost:8080/admin)
   * **Mailpit (Email Testing):** [http://localhost:8025](http://localhost:8025)

---

## 🔑 Default Accounts (Seeded)

All accounts share the default password: **`password`**

| Role | Email | Password | Access Area |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@exam.test` | `password` | `/admin` (Full Control) |
| **Proctor (Pengawas)** | `proctor@exam.test` | `password` | `/admin` (Monitor Attempts) |
| **Teacher (Guru)** | `teacher@exam.test` | `password` | `/admin` (Manage Exams & Questions) |
| **Student (Siswa)** | `student@exam.test` | `password` | `/` (Take Exams & View Results) |

---

## 🧪 Automated Testing

This application comes with a comprehensive test suite covering Authentication, Exam Services, Livewire components, and Filament resources.

Run the test suite:
```bash
php artisan test
```

Current test status: **29 tests, 89 assertions, 100% passed**.

---

## 🛠️ Tech Stack
* **Framework**: [Laravel 12 / 13](https://laravel.com) (PHP 8.4)
* **Admin Panel**: [Filament v4](https://filamentphp.com)
* **Frontend / Reactive UI**: [Livewire 3](https://livewire.laravel.com) + Alpine.js + Tailwind CSS
* **Database**: SQLite (local) / PostgreSQL 16 (docker)
* **Cache & Session**: File / Redis 7
* **Testing**: PHPUnit 11

---

## 📄 License
This software is open-sourced under the [MIT license](LICENSE).
