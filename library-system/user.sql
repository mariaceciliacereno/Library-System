-- Create the database and use it
CREATE DATABASE IF NOT EXISTS library_system;
USE library_system;

-- =========================
-- USERS TABLE
-- =========================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'member') NOT NULL DEFAULT 'member'
);

-- Insert default admin (password = admin123, hashed)
INSERT INTO users (email, password, role) VALUES
('admin@admin.com',
 '$2y$10$WjQrcPM1RWe1NQXtgnvA7uTg8cvA0sGTWXziNTcfmSrqZ.RApTh12',
 'admin')
ON DUPLICATE KEY UPDATE
 password = VALUES(password),
 role = 'admin';

-- =========================
-- BOOKS TABLE (with author and quantity)
-- =========================
CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(100) NOT NULL,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    number_of_books INT NOT NULL DEFAULT 1,
    available_books TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Sample books
INSERT INTO books (category, title, author, number_of_books, available_books) VALUES
('Technology',      'Data Structures in Java',   'John Smith',        5, 1),
('Philosophy',      'The Path of Wisdom',        'Sophia Lee',        3, 1),
('Business',        'Startup Secrets',           'Michael Adams',     4, 1),
('Science',         'A Brief History of Time',   'Stephen Hawking',   6, 1),
('Poetry',          'Words of the Soul',         'Emily Carter',      2, 1),
('History',         'The Ancient World',         'James Wilson',      5, 1),
('Biography',       'Steve Jobs',                'Walter Isaacson',   3, 1),
('Science Fiction', 'Interstellar Minds',        'Arthur Clarke',     4, 1),
('Fiction',         'The Lost Hero',             'Rick Riordan',      6, 1),
('Mystery',         'The Secret Door',           'Agatha Brown',      2, 1),
('Romance',         'Love in Paris',             'Isabella Moore',    5, 1),
('Technology',      'System Analysis and Design','Dennis Turner',     3, 1);

-- =========================
-- BORROW REQUESTS TABLE
-- =========================
CREATE TABLE IF NOT EXISTS borrow_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    borrowed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    returned_at DATETIME DEFAULT NULL,
    status ENUM('pending','approved','returned') DEFAULT 'pending',
    penalty DECIMAL(10,2) DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
);

-- =========================
-- MESSAGES TABLE
-- =========================
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender VARCHAR(100) NOT NULL,
    receiver VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- BOOK COPIES TABLE
-- =========================
CREATE TABLE IF NOT EXISTS book_copies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    copy_number INT NOT NULL,
    status ENUM('available','borrowed') DEFAULT 'available',
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    UNIQUE KEY unique_copy (book_id, copy_number)
);
