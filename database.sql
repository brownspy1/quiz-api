DROP DATABASE IF EXISTS quiz_db;
CREATE DATABASE quiz_db;
USE quiz_db;

CREATE TABLE api_keys (
  id INT AUTO_INCREMENT PRIMARY KEY,
  api_key VARCHAR(255) NOT NULL UNIQUE
);

INSERT INTO api_keys (api_key) VALUES ('YOUR_SECURE_API_KEY');
INSERT INTO api_keys (api_key) VALUES ('ccab1095203836787cf1bf95fe807899');

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default admin user: username: admin, password: admin123
INSERT INTO users (username, password) VALUES ('admin', '$2y$10$sKDV1kefsE1EaH3m2CZTBOb3GwTQVneE3Xr6xTGySfnlfHThf0tli');

CREATE TABLE questions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  question TEXT NOT NULL,
  option_a VARCHAR(255),
  option_b VARCHAR(255),
  option_c VARCHAR(255),
  option_d VARCHAR(255),
  correct_option CHAR(1),
  category VARCHAR(100),
  created_by_ai BOOLEAN DEFAULT 0
);