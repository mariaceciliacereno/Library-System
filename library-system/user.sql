-- Create the database and use it
CREATE DATABASE IF NOT EXISTS library_system;
USE library_system;

-- USERS TABLE
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

-- BOOKS TABLE (with author and quantity)
CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(100) NOT NULL,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    quantity INT NOT NULL DEFAULT 5
);

-- SAMPLE BOOKS
INSERT INTO books (category, title, author) VALUES
('Technology', 'Data Structures in Java', 'John Smith'),
('Philosophy', 'The Path of Wisdom', 'Sophia Lee'),
('Business', 'Startup Secrets', 'Michael Adams'),
('Science', 'A Brief History of Time', 'Stephen Hawking'),
('Poetry', 'Words of the Soul', 'Emily Carter'),
('History', 'The Ancient World', 'James Wilson'),
('Biography', 'Steve Jobs', 'Walter Isaacson'),
('Science Fiction', 'Interstellar Minds', 'Arthur Clarke'),
('Fiction', 'The Lost Hero', 'Rick Riordan'),
('Mystery', 'The Secret Door', 'Agatha Brown'),
('Romance', 'Love in Paris', 'Isabella Moore'),
('Technology', 'System Analysis and Design', 'Dennis Turner');

-- BORROW REQUESTS TABLE (use DATETIME; format for display in PHP)
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

CREATE TABLE messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sender VARCHAR(100) NOT NULL,
  receiver VARCHAR(100) NOT NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);



CREATE TABLE IF NOT EXISTS book_copies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    copy_number INT NOT NULL,
    status ENUM('available','borrowed') DEFAULT 'available',
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    UNIQUE KEY unique_copy (book_id, copy_number)
);
