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

CREATE TABLE IF NOT EXISTS roles (
  id                      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name                    VARCHAR(50) NOT NULL UNIQUE,
  created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS permissions (
  id                      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name                    VARCHAR(50) NOT NULL UNIQUE,
  created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS role_permissions (
  role_id                 INT UNSIGNED NOT NULL,
  permission_id           INT UNSIGNED NOT NULL,
  PRIMARY KEY (role_id, permission_id),
  FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
  FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO permissions (name) VALUES
('view_dashboard'),
('view_users'),
('manage_users'),
('access_moderator_page'),
('access_admin_page');

INSERT INTO roles (name) VALUES
('user'),
('moderator'),
('admin');

INSERT INTO role_permissions (role_id, permission_id)
    SELECT r.id, p.id
    FROM (
        SELECT 'user' AS role_name, 'view_dashboard' AS perm_name UNION ALL
        SELECT 'moderator', 'view_dashboard' UNION ALL
        SELECT 'moderator', 'view_users' UNION ALL
        SELECT 'moderator', 'access_moderator_page' UNION ALL
        SELECT 'admin', 'view_dashboard' UNION ALL
        SELECT 'admin', 'view_users' UNION ALL
        SELECT 'admin', 'manage_users' UNION ALL
        SELECT 'admin', 'access_moderator_page' UNION ALL
        SELECT 'admin', 'access_admin_page'
    ) AS seed_data
    JOIN roles r ON r.name = seed_data.role_name
    JOIN permissions p ON p.name = seed_data.perm_name; 

ALTER TABLE users 
    ADD COLUMN role_id INT UNSIGNED NULL;

UPDATE users 
    SET role_id = (SELECT id FROM roles WHERE name = 'user' LIMIT 1);

ALTER TABLE users 
    ADD CONSTRAINT fk_users_roles 
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT;
    
ALTER TABLE users 
    MODIFY COLUMN role_id INT UNSIGNED NOT NULL DEFAULT 1;

UPDATE users SET role_id = (SELECT id FROM roles WHERE name='admin') WHERE email='test@test.test';