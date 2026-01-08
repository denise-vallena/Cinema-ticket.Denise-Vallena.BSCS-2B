# Cinema Database System - Setup Guide

## Database Structure

The cinema database (`cinema_db`) consists of three main tables:

### 1. **users** - User Account Management
- Stores user credentials, roles, and wallet balances
- Fields: id, username, email, password, role, balance, created_at
- First registered user becomes admin
- Default balance: ₱1,000.00

### 2. **movies** - Movie Catalog
- Manages movie inventory with admin controls
- Fields: id, title, director, year, description, image, price, published, archived_at, created_at
- Supports publish/unpublish and archive/restore operations
- Minimum ticket price: ₱300.00

### 3. **tickets** - Purchase Records
- Tracks all ticket purchases with QR code support
- Fields: id, ticket_id, user_id, movie_id, price, payload, created_at
- Links users to movies with transaction history
- Stores JSON payload for receipt generation

## Quick Setup

### Option 1: Automatic Setup (Recommended)
The database is **automatically created** when you first access any page in the system.

1. Start XAMPP (Apache + MySQL)
2. Visit `http://localhost/cinema-database/cinema.php`
3. The system will create the database and tables automatically

### Option 2: Manual SQL Import

If you prefer to set up the database manually:

```bash
# From XAMPP MySQL command line or phpMyAdmin SQL tab
mysql -u root -p < schema.sql
```

Or using phpMyAdmin:
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Click "Import" tab
3. Select `schema.sql` file
4. Click "Go"

## Database Connection Settings

Located in `db.php`:
```php
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'cinema_db';
```

## First User Setup

The first user to sign up automatically becomes an admin:
1. Go to `http://localhost/cinema-database/signup.html`
2. Create your account
3. You will have admin privileges to add and manage movies

## Seeding Sample Movies

To populate the database with 30 sample movies:

1. Visit: `http://localhost/cinema-database/seed_movies.php`
2. The script will insert movies with published status
3. View them at: `http://localhost/cinema-database/cinema.php`

## Admin Panel Access

**URL:** `http://localhost/cinema-database/index.php`

**Features:**
- Add new movies with title, director, year, description, image, price
- Publish/Unpublish movies
- Archive/Restore movies
- Delete movies permanently
- Manage movie visibility

## User Features

**Account Page:** `http://localhost/cinema-database/user.php`

- View account balance
- Add balance (top-up)
- View purchase history with QR codes
- Become admin (for non-admin users)
- Demote to user (for admin users)

## Balance System

- **Initial Balance:** ₱1,000.00 per new user
- **Deductions:** Automatic when purchasing tickets
- **Top-up:** Use "Add Balance" form on account page
- **Persistence:** Balance is saved in database and survives logout

## Ticket System

Each ticket purchase:
- Generates unique ticket ID (format: `TKT-timestamp-random`)
- Deducts price from user balance in a transaction
- Creates QR code linking to ticket verification
- Stores complete receipt data in JSON format

## Useful SQL Queries

### View All Active Movies
```sql
SELECT id, title, director, year, price 
FROM movies 
WHERE published = 1 AND archived_at IS NULL;
```

### Check User Balances
```sql
SELECT email, role, balance 
FROM users 
ORDER BY created_at;
```

### View Recent Purchases
```sql
SELECT t.ticket_id, u.email, m.title, t.price, t.created_at
FROM tickets t
JOIN users u ON t.user_id = u.id
LEFT JOIN movies m ON t.movie_id = m.id
ORDER BY t.created_at DESC
LIMIT 20;
```

### Make User Admin
```sql
UPDATE users SET role = 'admin' WHERE email = 'user@example.com';
```

## Troubleshooting

### Database Not Created
- Ensure MySQL is running in XAMPP
- Check `db.php` connection settings
- Verify MySQL user has CREATE DATABASE privileges

### Tables Missing Columns
The system automatically adds missing columns on first run. If issues persist:
```sql
USE cinema_db;
SOURCE schema.sql;
```

### Permission Errors
Run MySQL with sufficient privileges:
```sql
GRANT ALL PRIVILEGES ON cinema_db.* TO 'root'@'localhost';
FLUSH PRIVILEGES;
```

## Maintenance

### Backup Database
```bash
mysqldump -u root cinema_db > cinema_backup_$(date +%Y%m%d).sql
```

### Restore Database
```bash
mysql -u root cinema_db < cinema_backup_20260108.sql
```

### Reset All Data
```sql
DROP DATABASE cinema_db;
-- Then visit any page to recreate automatically
```

## File Structure

```
cinema-database/
├── schema.sql           # Complete database schema
├── DATABASE_SETUP.md    # This file
├── db.php              # Connection & auto-setup
├── index.php           # Admin dashboard
├── cinema.php          # Public movie listing
├── user.php            # User account page
├── seed_movies.php     # Sample data script
└── [other PHP files]   # Endpoints and utilities
```

---

**Created by Denise Vallena**
