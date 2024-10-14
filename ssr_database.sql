CREATE DATABASE IF NOT EXISTS ssr;

USE ssr;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    port INT NOT NULL,
    traffic VARCHAR(255),
    used_traffic VARCHAR(255),
    remaining_traffic VARCHAR(255),
    total_traffic VARCHAR(255),
    ssr_link TEXT,
    converted_link TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
