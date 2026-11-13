SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS tomato_guide
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE tomato_guide;

-- 1. Vai tro quan tri va bien tap vien
CREATE TABLE IF NOT EXISTS roles (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name        VARCHAR(30) NOT NULL,
  description VARCHAR(255) NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_roles_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tai khoan nguoi dung. Luon luu password_hash(), khong luu mat khau thuan.
CREATE TABLE IF NOT EXISTS users (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  role_id       INT UNSIGNED NOT NULL,
  username      VARCHAR(50) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  full_name     VARCHAR(100) NOT NULL,
  email         VARCHAR(120) NULL,
  is_active     TINYINT(1) NOT NULL DEFAULT 1,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_username (username),
  UNIQUE KEY uq_users_email (email),
  CONSTRAINT fk_users_role
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Chuyen muc bai viet
CREATE TABLE IF NOT EXISTS categories (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name        VARCHAR(100) NOT NULL,
  slug        VARCHAR(120) NOT NULL,
  description TEXT NULL,
  sort_order  INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_categories_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Giai doan sinh truong cua cay ca chua
CREATE TABLE IF NOT EXISTS growth_stages (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name        VARCHAR(100) NOT NULL,
  slug        VARCHAR(120) NOT NULL,
  description TEXT NULL,
  sort_order  INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_growth_stages_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Bai viet huong dan canh tac
CREATE TABLE IF NOT EXISTS posts (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  category_id     INT UNSIGNED NOT NULL,
  growth_stage_id INT UNSIGNED NULL COMMENT 'NULL = bai viet chung',
  author_id       INT UNSIGNED NULL,
  title           VARCHAR(200) NOT NULL,
  slug            VARCHAR(220) NOT NULL,
  summary         VARCHAR(500) NULL,
  content         MEDIUMTEXT NOT NULL,
  thumbnail       VARCHAR(255) NULL,
  source          VARCHAR(255) NULL,
  status          ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
  views           INT UNSIGNED NOT NULL DEFAULT 0,
  created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_posts_slug (slug),
  KEY idx_posts_status_created (status, created_at),
  CONSTRAINT fk_posts_category
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
  CONSTRAINT fk_posts_growth_stage
    FOREIGN KEY (growth_stage_id) REFERENCES growth_stages(id) ON DELETE SET NULL,
  CONSTRAINT fk_posts_author
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL,
  FULLTEXT KEY ft_posts (title, summary, content)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Sau hai va benh tren cay ca chua
CREATE TABLE IF NOT EXISTS pests (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name        VARCHAR(150) NOT NULL,
  slug        VARCHAR(170) NOT NULL,
  type        ENUM('pest', 'disease') NOT NULL COMMENT 'pest = sau hai, disease = benh',
  description TEXT NULL,
  cause       TEXT NULL,
  prevention  TEXT NULL,
  treatment   TEXT NULL,
  source      VARCHAR(255) NULL,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_pests_slug (slug),
  FULLTEXT KEY ft_pests (name, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Trieu chung va bo phan bi anh huong
CREATE TABLE IF NOT EXISTS symptoms (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name        VARCHAR(150) NOT NULL,
  body_part   ENUM('leaf', 'stem', 'flower', 'fruit', 'root', 'whole_plant')
              NOT NULL DEFAULT 'leaf',
  description TEXT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_symptoms_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Quan he nhieu-nhieu giua sau benh va trieu chung
CREATE TABLE IF NOT EXISTS pest_symptoms (
  pest_id    INT UNSIGNED NOT NULL,
  symptom_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (pest_id, symptom_id),
  KEY idx_pest_symptoms_symptom (symptom_id),
  CONSTRAINT fk_pest_symptoms_pest
    FOREIGN KEY (pest_id) REFERENCES pests(id) ON DELETE CASCADE,
  CONSTRAINT fk_pest_symptoms_symptom
    FOREIGN KEY (symptom_id) REFERENCES symptoms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Anh gan voi bai viet hoac sau benh.
-- Moi anh chi gan voi mot trong hai loai doi tuong; PHP kiem tra quy tac nay.
CREATE TABLE IF NOT EXISTS images (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  file_path   VARCHAR(255) NOT NULL,
  alt_text    VARCHAR(200) NULL,
  post_id     INT UNSIGNED NULL,
  pest_id     INT UNSIGNED NULL,
  uploaded_by INT UNSIGNED NULL,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  CONSTRAINT fk_images_post
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
  CONSTRAINT fk_images_pest
    FOREIGN KEY (pest_id) REFERENCES pests(id) ON DELETE CASCADE,
  CONSTRAINT fk_images_uploader
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tao vai tro admin de tai khoan mac dinh co the lien ket.
INSERT INTO roles (name, description) VALUES
  ('admin', 'Quan tri vien: toan quyen, quan ly tai khoan')
ON DUPLICATE KEY UPDATE description = VALUES(description);

-- Tai khoan mac dinh: admin / Tomato@123.
INSERT INTO users (role_id, username, password_hash, full_name)
SELECT r.id, 'admin',
       '$2y$10$cZliAWxH2dLw7AxS8akYv.sQVgb/ylL.AAOKYyaVAtan50i7bA9by',
       'Quan tri vien'
FROM roles AS r
WHERE r.name = 'admin'
ON DUPLICATE KEY UPDATE
  role_id = VALUES(role_id),
  password_hash = VALUES(password_hash),
  full_name = VALUES(full_name),
  is_active = 1;
