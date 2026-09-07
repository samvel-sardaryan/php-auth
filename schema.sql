CREATE DATABASE IF NOT EXISTS php_auth
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'php_auth_user'@'localhost' IDENTIFIED BY 'Demo123!';
GRANT ALL PRIVILEGES ON php_auth.* TO 'php_auth_user'@'localhost';
FLUSH PRIVILEGES;

USE php_auth;

CREATE TABLE IF NOT EXISTS users (
  id                      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name                    VARCHAR(100) NOT NULL,
  email                   VARCHAR(255) NOT NULL,
  password_hash           VARCHAR(255) NOT NULL,
  created_at              TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  email_verified_at       DATETIME     NULL,
  verification_token      VARCHAR(64)  NULL,
  verification_expires_at DATETIME     NULL,
  reset_token             VARCHAR(64)  NULL,
  reset_expires_at        DATETIME     NULL,
  PRIMARY KEY (id),
  UNIQUE KEY email (email),
  KEY verification_token (verification_token),
  KEY reset_token (reset_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
