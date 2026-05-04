-- sql/schema.sql
-- Create database and tables for iBin - IoT - Based Smart Waste management for Himamaylan City auth and basic domain
CREATE DATABASE IF NOT EXISTS smartwaste CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE smartwaste;

-- Users table
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('Admin','User') NOT NULL DEFAULT 'Admin',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Example domain tables (optional; adjust to your project)
CREATE TABLE IF NOT EXISTS bins (
  bin_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50),
  location_lat DOUBLE,
  location_lng DOUBLE,
  type ENUM('Biodegradable','Recyclable','Residual'),
  fill_level INT DEFAULT 0,
  battery_level INT DEFAULT 100,
  last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS collection_routes (
  route_id INT AUTO_INCREMENT PRIMARY KEY,
  bin_id INT,
  status ENUM('Pending','Collected') DEFAULT 'Pending',
  assigned_to VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (bin_id) REFERENCES bins(bin_id)
) ENGINE=InnoDB;

-- Sensor calibration + adjustment storage (ultrasonic, YOLOv5 camera, NEO-6M GPS)
CREATE TABLE IF NOT EXISTS sensor_settings (
  id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
  settings_json LONGTEXT NOT NULL,
  updated_by VARCHAR(100) DEFAULT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO sensor_settings (id, settings_json)
VALUES (1, '{}')
ON DUPLICATE KEY UPDATE updated_at = updated_at;
