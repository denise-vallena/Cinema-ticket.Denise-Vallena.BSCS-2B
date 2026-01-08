# Cinema Database (simple PHP + MySQL example)

Place this folder in your XAMPP `htdocs` directory (example: `c:\xampp\htdocs\cinema-database`).

Steps:

- Start Apache and MySQL in XAMPP.
- Visit (admin dashboard): http://localhost/cinema-database/index.php
- Visit (public site): http://localhost/cinema-database/cinema.php

The PHP `db.php` file will attempt to create the database `cinema_db` and the `movies` table automatically if they don't exist.

Manual SQL (optional):

```sql
CREATE DATABASE cinema_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cinema_db;
CREATE TABLE movies (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  director VARCHAR(255) DEFAULT NULL,
  year SMALLINT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

Files:

- `index.php` — front-end HTML page
- `styles.css` — styles
- `script.js` — front-end JS (AJAX)
- `db.php` — database connection / setup
- `add.php` — endpoint to add a movie
- `fetch.php` — endpoint to list movies

If your MySQL uses a non-empty root password or a different user, edit `db.php` and change `$DB_USER` and `$DB_PASS` accordingly.

Authentication flow:

- Open `login.html` to sign in or `signup.html` to create an account.
- After signing in you'll be redirected to `index.php`. The movie UI is protected and requires login.

Admin behavior:

- The first account created becomes `admin`. Subsequent accounts are `user` by default.
- Admin-only pages like `admin_settings.php` remain available only to admins. The main site is `cinema.php`.

Files added for auth:

- `auth.php` — session helpers
- `signup.html`, `signup.php` — create an account
- `login.html`, `login.php` — sign in and create session
- `logout.php` — end session

Notes about email sign-up:

- The app now uses the user's email for account creation and login instead of a username.
- After a successful sign-up the user is automatically logged in and redirected to `index.php` to manage movies.
