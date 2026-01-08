<?php
// db.php - creates connection and ensures DB + table exist
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'cinema_db';

// Turn off mysqli exceptions so we can handle errors gracefully
mysqli_report(MYSQLI_REPORT_OFF);

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS);
if ($mysqli->connect_errno) {
  // Prefer a plain HTML/text message so pages including this file don't break
  http_response_code(500);
  die('Database connection failed: ' . htmlspecialchars($mysqli->connect_error));
}

// Ensure database exists
$createdb_sql = "CREATE DATABASE IF NOT EXISTS `$DB_NAME` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if (! $mysqli->query($createdb_sql)) {
    // If creation fails, continue and let select_db report the problem
}

// Select the database (fail explicitly if not available)
if (! $mysqli->select_db($DB_NAME)) {
  http_response_code(500);
  die("Unable to select database '$DB_NAME'. Please ensure MySQL is running and the user has privileges.");
}

// Create movies table if missing
$create = "CREATE TABLE IF NOT EXISTS movies (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  director VARCHAR(255) DEFAULT NULL,
  year SMALLINT NULL,
  description TEXT DEFAULT NULL,
  image VARCHAR(255) DEFAULT NULL,
  price DECIMAL(8,2) DEFAULT NULL,
  published TINYINT(1) DEFAULT 0,
  archived_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$mysqli->query($create);

// Create users table if missing (email-based auth). Make email unique.
$createUsers = "CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) DEFAULT NULL,
  email VARCHAR(255) DEFAULT NULL,
  role VARCHAR(20) DEFAULT 'user',
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$mysqli->query($createUsers);
// Ensure `role` column exists (for older DBs that were created before role was added)
$res = $mysqli->query("SHOW COLUMNS FROM users LIKE 'role'");
if (! $res || $res->num_rows === 0) {
    // best-effort: try to add the column
  @$mysqli->query("ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'user'");
}
// Ensure role default is 'user' (original behavior)
@$mysqli->query("ALTER TABLE users MODIFY role VARCHAR(20) DEFAULT 'user'");

// Ensure unique index on email exists
$res = $mysqli->query("SHOW INDEX FROM users WHERE Key_name = 'uq_users_email'");
if (! $res || $res->num_rows === 0) {
    // attempt to create unique index; ignore errors
    @$mysqli->query("CREATE UNIQUE INDEX uq_users_email ON users (email)");
}

// Ensure users have a balance column (decimal) for simple account balance tracking
$res = $mysqli->query("SHOW COLUMNS FROM users LIKE 'balance'");
if (! $res || $res->num_rows === 0) {
    @$mysqli->query("ALTER TABLE users ADD COLUMN balance DECIMAL(10,2) DEFAULT 1000.00");
}
// Initialize balance only when NULL (do not overwrite spent balances)
@$mysqli->query("UPDATE users SET balance = 1000.00 WHERE balance IS NULL");

// Create tickets table (to record purchases)
$createTickets = "CREATE TABLE IF NOT EXISTS tickets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ticket_id VARCHAR(120) NOT NULL,
  user_id INT NOT NULL,
  movie_id INT DEFAULT NULL,
  price DECIMAL(10,2) DEFAULT 0.00,
  payload JSON DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$mysqli->query($createTickets);

// If the movies table exists but lacks columns, try adding them (best-effort)
@$mysqli->query("ALTER TABLE movies ADD COLUMN description TEXT DEFAULT NULL");
@$mysqli->query("ALTER TABLE movies ADD COLUMN image VARCHAR(255) DEFAULT NULL");
@$mysqli->query("ALTER TABLE movies ADD COLUMN price DECIMAL(8,2) DEFAULT NULL");
@$mysqli->query("ALTER TABLE movies ADD COLUMN published TINYINT(1) DEFAULT 0");
@$mysqli->query("ALTER TABLE movies ADD COLUMN archived_at DATETIME DEFAULT NULL");

