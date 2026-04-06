# AttendTrack — LAMP Hosting Deployment Guide

Attendance management system with GPS check-in, selfie capture, overtime tracking, and admin reporting.
Rewritten for **standard shared hosting** (Apache + PHP 8 + MySQL).

---

## 📁 Project Structure

```
attendtrack/
├── .htaccess              ← Apache URL rewriting (clean URLs)
├── index.php              ← Login page
├── dashboard.php          ← User attendance dashboard
├── register.php           ← New account registration
├── profile.php            ← Edit profile, photo, password
├── forgot-password.php    ← Token-based password reset
├── logout.php             ← Session destroy + redirect
├── attendance-action.php  ← JSON API for check-in/out
├── install.php            ← One-click DB installer ⚠️ DELETE AFTER USE
├── config.php             ← DB config (edit before deploying)
├── functions.php          ← All app functions (MySQL)
├── schema_mysql.sql       ← MySQL schema (alternative to installer)
├── includes/
│   └── nav.php            ← Shared sidebar/topbar
├── admin/
│   ├── index.php          ← Admin dashboard (Chart.js)
│   ├── users.php          ← User CRUD + work schedules
│   └── reports.php        ← Attendance reports + CSV/Excel export
└── assets/
    ├── css/style.css      ← Full stylesheet
    └── logo.svg           ← App logo
```

---

## 🚀 Deployment Steps

### Option A — One-Click Installer (Recommended)

1. Upload all files to your hosting `public_html/` (or subdirectory)
2. Visit `https://yourdomain.com/install.php`
3. Enter your MySQL credentials and click **Run Installation**
4. The installer creates the database, tables, seeds sample data, and patches `config.php`
5. **Delete `install.php` after installation**

### Option B — Manual Setup

1. Edit `config.php` with your MySQL credentials:
   ```php
   define('DB_HOST',  'localhost');
   define('DB_NAME',  'attendtrack');
   define('DB_USER',  'your_username');
   define('DB_PASS',  'your_password');
   ```

2. Create the database in phpMyAdmin, then import `schema_mysql.sql`

3. Upload all files to your hosting

---

## ⚙️ Requirements

| Requirement | Minimum |
|---|---|
| PHP | 8.0+ |
| MySQL | 5.7+ or MariaDB 10.3+ |
| PDO MySQL extension | Required |
| Apache mod_rewrite | Optional (for clean URLs) |

---

## 🔑 Default Accounts

| Role | Username | Password |
|---|---|---|
| Admin | `admin` | `Admin@123` |
| User | `john_doe` | `User@123` |
| User | `jane_smith` | `User@123` |
| User | `ali_rahman` | `User@123` |
| User | `siti_nurhaliza` | `User@123` |
| User | `budi_santoso` | `User@123` |

> ⚠️ Change all passwords immediately after first login.

---

## 🌐 URL Routes

| URL | Page |
|---|---|
| `/index.php` | Login |
| `/dashboard.php` | User dashboard |
| `/register.php` | Register |
| `/profile.php` | My profile |
| `/forgot-password.php` | Password reset |
| `/logout.php` | Logout |
| `/admin/index.php` | Admin dashboard |
| `/admin/users.php` | User management |
| `/admin/reports.php` | Reports & export |
| `/attendance-action.php` | Check-in/out API (POST) |

> If mod_rewrite is enabled, clean URLs also work:
> `/dashboard`, `/admin/index`, etc.

---

## ✨ Features

- 🔐 Login / Register / Forgot Password
- 📍 GPS-verified check-in & check-out
- 📸 Selfie photo at every check-in (stored as base64 in DB)
- ⏱️ Overtime check-in & check-out
- 🕐 Live clock display
- 📊 Admin dashboard with Chart.js weekly trend
- 👥 User management (CRUD + work schedules)
- 📑 Attendance reports with date/user filters
- 📥 Export to CSV and Excel (.xls)
- 👤 Profile editing, password change, photo upload
- 📱 Fully responsive design

---

## 🔄 Changes From Original (Vercel/PostgreSQL)

| Original | This Version |
|---|---|
| PostgreSQL | MySQL / MariaDB |
| Vercel serverless | Standard LAMP hosting |
| DB-stored sessions | Native PHP file sessions |
| `SERIAL` | `INT AUTO_INCREMENT` |
| `DATE_TRUNC()` | `YEAR()` / `MONTH()` |
| `EXTRACT(EPOCH ...)` | `TIME_TO_SEC(TIMEDIFF(...))` |
| `RETURNING id` | `lastInsertId()` |
| `INTERVAL '1 hour'` | `INTERVAL 1 HOUR` |
| `ON CONFLICT` | `INSERT IGNORE` |
| Vercel routing | `.htaccess` mod_rewrite |
| Clean URL links | `.php` extension links |

---

## 📄 License

MIT — free to use and modify.
