# Deposit System (PHP + MySQL)

A minimal deposit/investment style web app with **User Panel** and **Admin Panel**.

## Features
- Registration & Login (password hashed)
- User:
  - View & buy offers (price + daily bonus)
  - Auto-credit daily bonus once per day (when user opens dashboard) or via admin button
  - Wallet (balance) + Transaction history
  - Deposit request / Withdraw request
  - Edit profile
- Admin:
  - Dashboard metrics
  - User list (balance adjust, ban/unban)
  - Offer CRUD
  - Approve/Reject Deposits and Withdrawals
  - Run daily bonus for all users

> This is a starter. Improve styling, validations, and security before production.

## Requirements
- PHP 8+
- MySQL 5.7+/MariaDB
- Apache or Nginx
- `mysqli` extension

## Setup
1. Create DB and tables:
   - Import `sql/database.sql` into MySQL
2. Configure DB:
   - Edit `config/db.php` with your credentials
3. Create an admin user (any of):
   - In MySQL: `UPDATE users SET role='admin' WHERE email='you@example.com';`
   - Or insert a new row with role='admin'.
4. Run locally (XAMPP/Laragon) or upload to hosting.
5. Optional cron (daily bonus):
   - Hit `admin/run_bonus.php` daily with a cron job OR rely on user logins (dashboard triggers credit).

Default pages:
- `/index.php` (login)
- `/register.php`
- `/dashboard.php` (user)
- `/admin/index.php` (admin login -> redirect to dashboard)

## Security Notes
- Uses prepared statements for DB writes.
- CSRF not implemented (add before production).
- Payment gateway not included (manual admin approval flow).
- Email/SMS not included.
