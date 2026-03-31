-- resqnet Database Schema
-- Run this file against your MySQL database to set up the tables.

CREATE DATABASE IF NOT EXISTS resqnet
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE resqnet;

-- Users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('general_public', 'grama_niladhari', 'ngo', 'dmc_admin') DEFAULT 'general_public',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Early warnings
CREATE TABLE IF NOT EXISTS warnings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    message TEXT NOT NULL,
    location VARCHAR(180) NOT NULL,
    severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    status ENUM('draft', 'published') DEFAULT 'draft',
    issued_by INT NULL,
    issued_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (issued_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Donation appeals
CREATE TABLE IF NOT EXISTS donation_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    needed_location VARCHAR(180) NOT NULL,
    target_amount DECIMAL(12, 2) NOT NULL DEFAULT 0,
    collected_amount DECIMAL(12, 2) NOT NULL DEFAULT 0,
    status ENUM('open', 'closed', 'fulfilled') DEFAULT 'open',
    created_by INT NULL,
    assigned_ngo INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_ngo) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Individual contributions
CREATE TABLE IF NOT EXISTS donations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donation_request_id INT NOT NULL,
    donor_id INT NULL,
    donor_name VARCHAR(120) NOT NULL,
    donor_email VARCHAR(150) NULL,
    amount DECIMAL(12, 2) NOT NULL,
    message VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (donation_request_id) REFERENCES donation_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (donor_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Seed data (optional, for testing)
-- Shared password for all seeded users below: admin123
INSERT INTO users (name, email, password, role) VALUES
('DMC Administrator', 'dmc@resqnet.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dmc_admin'),
('Grama Niladhari Officer', 'gn@resqnet.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'grama_niladhari'),
('Relief NGO Coordinator', 'ngo@resqnet.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ngo'),
('Registered Public User', 'public@resqnet.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'general_public');

INSERT INTO warnings (title, message, location, severity, status, issued_by, issued_at) VALUES
('Heavy Rain Alert', 'Expect severe rainfall and possible flash floods in low-lying areas.', 'Kalutara District', 'high', 'published', 2, NOW()),
('Landslide Watch', 'Ground instability observed near slope-side settlements. Stay alert.', 'Badulla District', 'medium', 'published', 2, NOW()),
('Reservoir Spill Notice', 'Controlled spill release expected. Keep away from riverbanks.', 'Kandy District', 'critical', 'published', 1, NOW());

INSERT INTO donation_requests (title, description, needed_location, target_amount, collected_amount, status, created_by, assigned_ngo) VALUES
('Flood Relief Essentials', 'Immediate support needed for dry rations, hygiene kits, and temporary bedding for displaced families.', 'Kalutara District', 500000.00, 25000.00, 'open', 1, 3),
('School Recovery Kits', 'Support children affected by recent floods with books, uniforms, and school supplies.', 'Gampaha District', 300000.00, 15000.00, 'open', 3, 3);

INSERT INTO donations (donation_request_id, donor_id, donor_name, donor_email, amount, message) VALUES
(1, 4, 'Registered Public User', 'public@resqnet.com', 25000.00, 'Stay strong. We are with you.'),
(2, NULL, 'Anonymous Supporter', 'supporter@example.com', 15000.00, 'For affected children.');
