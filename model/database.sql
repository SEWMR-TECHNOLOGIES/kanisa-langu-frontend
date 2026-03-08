CREATE DATABASE IF NOT EXISTS kanisalangu;
USE kanisalangu;

CREATE TABLE IF NOT EXISTS kanisalangu_admins (
    kanisalangu_admin_id INT AUTO_INCREMENT PRIMARY KEY,
    kanisalangu_admin_username VARCHAR(100) NOT NULL UNIQUE,
    kanisalangu_admin_password VARCHAR(255) NOT NULL,
    kanisalangu_admin_role ENUM('super_admin', 'admin') NOT NULL,
    kanisalangu_admin_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS kanisalangu_admin_logins (
    login_id INT AUTO_INCREMENT PRIMARY KEY,
    kanisalangu_admin_id INT NOT NULL,
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    FOREIGN KEY (kanisalangu_admin_id) REFERENCES kanisalangu_admins(kanisalangu_admin_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS regions (
    region_id INT AUTO_INCREMENT PRIMARY KEY,
    region_name VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS districts (
    district_id INT AUTO_INCREMENT PRIMARY KEY,
    district_name VARCHAR(100) NOT NULL,
    region_id INT,
    FOREIGN KEY (region_id) REFERENCES regions(region_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS dioceses (
    diocese_id INT AUTO_INCREMENT PRIMARY KEY,
    diocese_name VARCHAR(100) NOT NULL UNIQUE,
    region_id INT,
    district_id INT,
    diocese_address VARCHAR(255) NULL,
    diocese_email VARCHAR(100) NULL,
    diocese_phone VARCHAR(50) NULL,
    FOREIGN KEY (region_id) REFERENCES regions(region_id) ON DELETE SET NULL,
    FOREIGN KEY (district_id) REFERENCES districts(district_id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS diocese_admins (
    diocese_admin_id INT AUTO_INCREMENT PRIMARY KEY,
    diocese_admin_fullname VARCHAR(100) NOT NULL,
    diocese_admin_email VARCHAR(255) NOT NULL,
    diocese_admin_phone VARCHAR(50),
    diocese_admin_role ENUM('admin', 'bishop', 'secretary', 'chairperson') NOT NULL,
    diocese_admin_password VARCHAR(255) DEFAULT '$2y$10$6uEMKARLh9HbnZefxvKuKOsg13zZXKoo17/rQOuXPUE0pk7ojN6qW',
    diocese_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (diocese_id) REFERENCES dioceses(diocese_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS provinces (
    province_id INT AUTO_INCREMENT PRIMARY KEY,
    province_name VARCHAR(100) NOT NULL UNIQUE,
    diocese_id INT NOT NULL,
    region_id INT,
    district_id INT,
    province_address VARCHAR(255) NULL,
    province_email VARCHAR(100) NULL,
    province_phone VARCHAR(50) NULL,
    FOREIGN KEY (diocese_id) REFERENCES dioceses(diocese_id) ON DELETE CASCADE,
    FOREIGN KEY (region_id) REFERENCES regions(region_id) ON DELETE SET NULL,
    FOREIGN KEY (district_id) REFERENCES districts(district_id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS province_admins (
    province_admin_id INT AUTO_INCREMENT PRIMARY KEY,
    province_admin_fullname VARCHAR(100) NOT NULL,
    province_admin_email VARCHAR(255) NOT NULL,
    province_admin_phone VARCHAR(50),
    province_admin_role ENUM('admin', 'bishop', 'secretary', 'chairperson') NOT NULL,
    province_admin_password VARCHAR(255) DEFAULT '$2y$10$6uEMKARLh9HbnZefxvKuKOsg13zZXKoo17/rQOuXPUE0pk7ojN6qW',
    province_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (province_id) REFERENCES provinces(province_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS head_parishes (
    head_parish_id INT AUTO_INCREMENT PRIMARY KEY,
    head_parish_name VARCHAR(100) NOT NULL,
    head_parish_address VARCHAR(255) NOT NULL,
    head_parish_email VARCHAR(100) NOT NULL,
    head_parish_phone VARCHAR(50),
    diocese_id INT NOT NULL,
    province_id INT NOT NULL,
    region_id INT,
    district_id INT,
    head_parish_website VARCHAR(255),
    FOREIGN KEY (diocese_id) REFERENCES dioceses(diocese_id) ON DELETE CASCADE,
    FOREIGN KEY (province_id) REFERENCES provinces(province_id) ON DELETE CASCADE,
    FOREIGN KEY (region_id) REFERENCES regions(region_id) ON DELETE CASCADE,
    FOREIGN KEY (district_id) REFERENCES districts(district_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS head_parish_admins (
    head_parish_admin_id INT AUTO_INCREMENT PRIMARY KEY,
    head_parish_admin_fullname VARCHAR(100) NOT NULL,
    head_parish_admin_email VARCHAR(255) NOT NULL,
    head_parish_admin_phone VARCHAR(50),
    head_parish_admin_role ENUM('admin', 'pastor', 'secretary', 'chairperson') NOT NULL,
    head_parish_admin_password VARCHAR(255) DEFAULT '$2y$10$6uEMKARLh9HbnZefxvKuKOsg13zZXKoo17/rQOuXPUE0pk7ojN6qW',
    head_parish_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (head_parish_id) REFERENCES head_parishes(head_parish_id) ON DELETE CASCADE
);


CREATE TABLE IF NOT EXISTS admin_login_records (
    record_id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    admin_type ENUM('kanisalangu_admin', 'diocese_admin', 'province_admin', 'head_parish_admin') NOT NULL,
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    first_login BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (admin_id) REFERENCES kanisalangu_admins(kanisalangu_admin_id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES diocese_admins(diocese_admin_id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES province_admins(province_admin_id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES head_parish_admins(head_parish_admin_id) ON DELETE CASCADE
);


-- Table to store password reset codes
CREATE TABLE IF NOT EXISTS admin_password_reset_codes (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    admin_type ENUM('kanisalangu_admin', 'diocese_admin', 'province_admin', 'head_parish_admin') NOT NULL,
    reset_code VARCHAR(255) NOT NULL,
    request_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expiration_time TIMESTAMP NULL,
    used BOOLEAN DEFAULT FALSE
);
