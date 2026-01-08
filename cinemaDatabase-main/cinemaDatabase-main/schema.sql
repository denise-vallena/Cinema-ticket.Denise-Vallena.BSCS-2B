-- ============================================================================
-- Cinema Database System - Complete Schema
-- ============================================================================
-- Database: cinema_db
-- Description: Movie ticketing system with user management, balance tracking,
--              and ticket purchase functionality
-- Created: January 8, 2026
-- ============================================================================

-- Create database
CREATE DATABASE IF NOT EXISTS cinema_db 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE cinema_db;

-- ============================================================================
-- Table: users
-- Description: Stores user accounts with authentication and balance tracking
-- ============================================================================
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) DEFAULT NULL COMMENT 'Optional display name',
  email VARCHAR(255) NOT NULL COMMENT 'Unique email for login',
  password VARCHAR(255) NOT NULL COMMENT 'Hashed password',
  role VARCHAR(20) DEFAULT 'user' COMMENT 'User role: user or admin',
  balance DECIMAL(10,2) DEFAULT 1000.00 COMMENT 'Account balance in pesos',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Account creation timestamp',
  UNIQUE KEY uq_users_email (email),
  INDEX idx_role (role),
  INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='User accounts with authentication and wallet balance';

-- ============================================================================
-- Table: movies
-- Description: Movie catalog with publishing and archiving capabilities
-- ============================================================================
CREATE TABLE IF NOT EXISTS movies (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL COMMENT 'Movie title',
  director VARCHAR(255) DEFAULT NULL COMMENT 'Director name',
  year SMALLINT DEFAULT NULL COMMENT 'Release year',
  description TEXT DEFAULT NULL COMMENT 'Movie description/synopsis',
  image VARCHAR(255) DEFAULT NULL COMMENT 'Poster image URL or path',
  price DECIMAL(8,2) DEFAULT NULL COMMENT 'Ticket price in pesos',
  published TINYINT(1) DEFAULT 0 COMMENT 'Publication status: 0=unpublished, 1=published',
  archived_at DATETIME DEFAULT NULL COMMENT 'Archive timestamp (NULL if active)',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Movie creation timestamp',
  INDEX idx_published (published),
  INDEX idx_archived (archived_at),
  INDEX idx_title (title)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Movie catalog with admin controls for publishing/archiving';

-- ============================================================================
-- Table: tickets
-- Description: Purchase records linking users to movies with transaction data
-- ============================================================================
CREATE TABLE IF NOT EXISTS tickets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ticket_id VARCHAR(120) NOT NULL COMMENT 'Unique ticket identifier (TKT-...)',
  user_id INT NOT NULL COMMENT 'Foreign key to users table',
  movie_id INT DEFAULT NULL COMMENT 'Foreign key to movies table (NULL for placeholder)',
  price DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Purchase price at time of transaction',
  payload JSON DEFAULT NULL COMMENT 'JSON ticket data (buyer, movie title, timestamp)',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Purchase timestamp',
  UNIQUE KEY uq_ticket_id (ticket_id),
  INDEX idx_user_id (user_id),
  INDEX idx_movie_id (movie_id),
  INDEX idx_created_at (created_at),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Ticket purchase records with QR code support';

-- ============================================================================
-- Initial Data Setup
-- ============================================================================

-- Set first user as admin (run after first signup)
-- UPDATE users SET role = 'admin' WHERE id = 1;

-- Initialize NULL balances to default 1000.00
UPDATE users SET balance = 1000.00 WHERE balance IS NULL;

-- ============================================================================
-- Useful Queries
-- ============================================================================

-- View all published movies with prices
-- SELECT id, title, director, year, price, created_at 
-- FROM movies 
-- WHERE published = 1 AND archived_at IS NULL 
-- ORDER BY created_at DESC;

-- View user balances
-- SELECT id, email, role, balance, created_at 
-- FROM users 
-- ORDER BY created_at ASC;

-- View recent ticket purchases
-- SELECT t.ticket_id, u.email, m.title, t.price, t.created_at
-- FROM tickets t
-- JOIN users u ON t.user_id = u.id
-- LEFT JOIN movies m ON t.movie_id = m.id
-- ORDER BY t.created_at DESC
-- LIMIT 50;

-- Check admin users
-- SELECT id, email, role, created_at 
-- FROM users 
-- WHERE role = 'admin';

-- ============================================================================
-- Maintenance Queries
-- ============================================================================

-- Archive old movies (example: movies older than 2 years)
-- UPDATE movies 
-- SET archived_at = NOW() 
-- WHERE year < YEAR(NOW()) - 2 AND archived_at IS NULL;

-- Restore archived movie
-- UPDATE movies SET archived_at = NULL WHERE id = ?;

-- Delete permanently archived movies (older than 1 year in archive)
-- DELETE FROM movies 
-- WHERE archived_at IS NOT NULL 
-- AND archived_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);

-- ============================================================================
-- End of Schema
-- ============================================================================
