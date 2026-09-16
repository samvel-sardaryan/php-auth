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
  created_at              DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
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
  created_at              DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS permissions (
  id                      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name                    VARCHAR(50) NOT NULL UNIQUE,
  created_at              DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP,
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

CREATE TABLE IF NOT EXISTS profiles (
  user_id                 INT UNSIGNED NOT NULL,
  first_name              VARCHAR(100) NULL,
  last_name               VARCHAR(100) NULL,
  phone                   VARCHAR(30)  NULL,
  location                VARCHAR(100) NULL,
  date_of_birth           DATE         NULL,
  bio                     TEXT         NULL,
  avatar                  VARCHAR(255) NULL,
  PRIMARY KEY (user_id),
  CONSTRAINT fk_profiles_users FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS posts (
  id                      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id                 INT UNSIGNED NOT NULL,
  title                   VARCHAR(150) NOT NULL,
  content                 TEXT         NOT NULL,
  created_at              DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  deleted_at              DATETIME     NULL,
  PRIMARY KEY (id),
  KEY idx_posts_author (user_id, deleted_at),
  KEY idx_posts_feed (deleted_at, created_at),
  CONSTRAINT fk_posts_users FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO permissions (name) VALUES ('manage_posts');

INSERT INTO role_permissions (role_id, permission_id)
    SELECT r.id, p.id
    FROM roles r
    JOIN permissions p ON p.name = 'manage_posts'
    WHERE r.name = 'admin';

CREATE TABLE IF NOT EXISTS categories (
  id                      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name                    VARCHAR(50)  NOT NULL,
  slug                    VARCHAR(50)  NOT NULL,
  is_default              TINYINT(1)   NOT NULL DEFAULT 0,
  created_at              DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_categories_slug (slug),
  UNIQUE KEY uq_categories_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO categories (name, slug, is_default) VALUES
('News',     'news',     0),
('Tutorial', 'tutorial', 0),
('Question', 'question', 0),
('Other',    'other',    1);

CREATE TABLE IF NOT EXISTS tags (
  id                      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name                    VARCHAR(30)  NOT NULL,
  created_at              DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_tags_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE posts
    ADD COLUMN status ENUM('draft','published','archived') NOT NULL DEFAULT 'draft' AFTER content;

UPDATE posts SET status = 'published';

ALTER TABLE posts
    ADD COLUMN category_id INT UNSIGNED NULL AFTER status;

UPDATE posts
    SET category_id = (SELECT id FROM categories WHERE is_default = 1 LIMIT 1);

ALTER TABLE posts
    MODIFY COLUMN category_id INT UNSIGNED NOT NULL;

ALTER TABLE posts
    ADD CONSTRAINT fk_posts_categories
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT;

ALTER TABLE posts
    ADD KEY idx_posts_feed_status (status, deleted_at, created_at),
    ADD KEY idx_posts_author_status (user_id, status, deleted_at),
    ADD KEY idx_posts_category (category_id);

CREATE TABLE IF NOT EXISTS post_tag (
  post_id                 INT UNSIGNED NOT NULL,
  tag_id                  INT UNSIGNED NOT NULL,
  PRIMARY KEY (post_id, tag_id),
  KEY idx_post_tag_tag (tag_id),
  CONSTRAINT fk_post_tag_posts FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
  CONSTRAINT fk_post_tag_tags  FOREIGN KEY (tag_id)  REFERENCES tags(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS post_images (
  id                      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  post_id                 INT UNSIGNED NOT NULL,
  path                    VARCHAR(255) NOT NULL,
  created_at              DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_post_images_post (post_id),
  CONSTRAINT fk_post_images_posts FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS comments (
  id                      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  post_id                 INT UNSIGNED NOT NULL,
  user_id                 INT UNSIGNED NOT NULL,
  parent_id               INT UNSIGNED NULL,
  content                 TEXT         NOT NULL,
  created_at              DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  deleted_at              DATETIME     NULL,
  PRIMARY KEY (id),
  KEY idx_comments_thread (post_id, deleted_at, created_at),
  KEY idx_comments_parent (parent_id),
  KEY idx_comments_author (user_id),
  CONSTRAINT fk_comments_posts  FOREIGN KEY (post_id)   REFERENCES posts(id)    ON DELETE CASCADE,
  CONSTRAINT fk_comments_users  FOREIGN KEY (user_id)   REFERENCES users(id)    ON DELETE CASCADE,
  CONSTRAINT fk_comments_parent FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS post_likes (
  user_id                 INT UNSIGNED NOT NULL,
  post_id                 INT UNSIGNED NOT NULL,
  created_at              DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, post_id),
  KEY idx_post_likes_post (post_id),
  CONSTRAINT fk_post_likes_users FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_post_likes_posts FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS reports (
  id                      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  reporter_id             INT UNSIGNED NOT NULL,
  target_type             ENUM('post','comment') NOT NULL,
  target_id               INT UNSIGNED NOT NULL,
  reason                  VARCHAR(255) NOT NULL,
  created_at              DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_reports_once (reporter_id, target_type, target_id),
  KEY idx_reports_target (target_type, target_id),
  KEY idx_reports_queue (created_at),
  CONSTRAINT fk_reports_users FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS login_attempts (
  id                      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  email                   VARCHAR(255) NOT NULL,
  ip_address              VARCHAR(45)  NOT NULL,
  created_at              DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_login_attempts_email (email, created_at),
  KEY idx_login_attempts_ip (ip_address, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS activity_log (
  id                      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id                 INT UNSIGNED NULL,
  action                  VARCHAR(50)  NOT NULL,
  target_type             VARCHAR(20)  NULL,
  target_id               INT UNSIGNED NULL,
  ip_address              VARCHAR(45)  NULL,
  created_at              DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_activity_user (user_id, created_at),
  KEY idx_activity_recent (created_at),
  CONSTRAINT fk_activity_users FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO permissions (name) VALUES
('create_post'),
('moderate_posts'),
('moderate_comments'),
('view_deleted_posts');

INSERT INTO role_permissions (role_id, permission_id)
    SELECT r.id, p.id
    FROM (
        SELECT 'user'      AS role_name, 'create_post'        AS perm_name UNION ALL
        SELECT 'moderator', 'create_post'        UNION ALL
        SELECT 'moderator', 'moderate_posts'     UNION ALL
        SELECT 'moderator', 'moderate_comments'  UNION ALL
        SELECT 'admin',     'create_post'        UNION ALL
        SELECT 'admin',     'moderate_posts'     UNION ALL
        SELECT 'admin',     'moderate_comments'  UNION ALL
        SELECT 'admin',     'view_deleted_posts'
    ) AS seed_data
    JOIN roles r       ON r.name = seed_data.role_name
    JOIN permissions p ON p.name = seed_data.perm_name;
