-- ============================================================================
-- So tay huong dan canh tac ca chua
-- MySQL 5.7+ / MariaDB 10.3+, InnoDB, UTF-8
-- Import file nay truoc sample_data.sql va setup_admin.php.
-- ============================================================================

CREATE DATABASE IF NOT EXISTS tomato_guide
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE tomato_guide;
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Cac bang duoc tao neu chua ton tai; file nay khong xoa du lieu.
-- Neu da co database cu, hay backup va tao database moi truoc khi import
-- de nhan day du cac cot va chi muc moi.

CREATE TABLE IF NOT EXISTS roles (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name        VARCHAR(30) NOT NULL,
  description VARCHAR(255) NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_roles_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Vai tro tai khoan quan tri';

CREATE TABLE IF NOT EXISTS users (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  role_id       INT UNSIGNED NOT NULL,
  username      VARCHAR(50) NOT NULL,
  password_hash VARCHAR(255) NOT NULL COMMENT 'Hash tao boi password_hash() cua PHP',
  full_name     VARCHAR(100) NOT NULL,
  email         VARCHAR(120) NULL,
  is_active     TINYINT(1) NOT NULL DEFAULT 1,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_username (username),
  UNIQUE KEY uq_users_email (email),
  KEY idx_users_role_active (role_id, is_active),
  CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles (id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Tai khoan admin va bien tap vien';

CREATE TABLE IF NOT EXISTS categories (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name        VARCHAR(100) NOT NULL,
  slug        VARCHAR(120) NOT NULL,
  description TEXT NULL,
  sort_order  INT NOT NULL DEFAULT 0,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_categories_slug (slug),
  KEY idx_categories_order (sort_order, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Chuyen muc bai viet';

CREATE TABLE IF NOT EXISTS growth_stages (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name        VARCHAR(100) NOT NULL,
  slug        VARCHAR(120) NOT NULL,
  description TEXT NULL,
  sort_order  INT NOT NULL DEFAULT 0,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_growth_stages_slug (slug),
  KEY idx_growth_stages_order (sort_order, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Cac giai doan sinh truong cua cay ca chua';

CREATE TABLE IF NOT EXISTS posts (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  category_id     INT UNSIGNED NOT NULL,
  growth_stage_id INT UNSIGNED NULL COMMENT 'NULL neu bai viet ap dung chung',
  author_id       INT UNSIGNED NULL,
  title           VARCHAR(200) NOT NULL,
  slug            VARCHAR(220) NOT NULL,
  summary         VARCHAR(500) NULL,
  content         MEDIUMTEXT NOT NULL,
  thumbnail       VARCHAR(255) NULL,
  source          VARCHAR(255) NULL COMMENT 'Nguon tham khao',
  status          ENUM('draft','published') NOT NULL DEFAULT 'draft',
  views           INT UNSIGNED NOT NULL DEFAULT 0,
  created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_posts_slug (slug),
  KEY idx_posts_status_created (status, created_at, id),
  KEY idx_posts_category_status_created (category_id, status, created_at),
  KEY idx_posts_stage_status_created (growth_stage_id, status, created_at),
  KEY idx_posts_author (author_id),
  FULLTEXT KEY ft_posts_search (title, summary, content),
  CONSTRAINT fk_posts_category FOREIGN KEY (category_id) REFERENCES categories (id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_posts_growth_stage FOREIGN KEY (growth_stage_id) REFERENCES growth_stages (id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_posts_author FOREIGN KEY (author_id) REFERENCES users (id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Bai viet huong dan canh tac';

CREATE TABLE IF NOT EXISTS pests (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name        VARCHAR(150) NOT NULL,
  slug        VARCHAR(170) NOT NULL,
  type        ENUM('pest','disease') NOT NULL COMMENT 'Pest la sau hai; disease la benh',
  description TEXT NULL,
  cause       TEXT NULL,
  prevention  TEXT NULL,
  treatment   TEXT NULL,
  source      VARCHAR(255) NULL COMMENT 'Nguon tham khao',
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_pests_slug (slug),
  KEY idx_pests_type_created (type, created_at),
  FULLTEXT KEY ft_pests_search (name, description),
  FULLTEXT KEY ft_pests_all_text (name, description, cause, prevention, treatment)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Thong tin sau hai va benh tren ca chua';

CREATE TABLE IF NOT EXISTS symptoms (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name        VARCHAR(150) NOT NULL,
  body_part   ENUM('leaf','stem','flower','fruit','root','whole_plant') NOT NULL DEFAULT 'leaf'
              COMMENT 'Bo phan tren cay co bieu hien',
  description TEXT NULL,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_symptoms_name (name),
  KEY idx_symptoms_body_part_name (body_part, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Danh muc trieu chung';

CREATE TABLE IF NOT EXISTS pest_symptoms (
  pest_id    INT UNSIGNED NOT NULL,
  symptom_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (pest_id, symptom_id),
  KEY idx_pest_symptoms_symptom (symptom_id, pest_id),
  CONSTRAINT fk_pest_symptoms_pest FOREIGN KEY (pest_id) REFERENCES pests (id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_pest_symptoms_symptom FOREIGN KEY (symptom_id) REFERENCES symptoms (id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Lien ket nhieu-nhieu giua sau benh va trieu chung';

CREATE TABLE IF NOT EXISTS images (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  file_path   VARCHAR(255) NOT NULL COMMENT 'Duong dan tuong doi trong uploads/',
  alt_text    VARCHAR(200) NULL,
  post_id     INT UNSIGNED NULL,
  pest_id     INT UNSIGNED NULL,
  uploaded_by INT UNSIGNED NULL,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_images_post (post_id, id),
  KEY idx_images_pest (pest_id, id),
  KEY idx_images_uploaded_by (uploaded_by),
  CONSTRAINT fk_images_post FOREIGN KEY (post_id) REFERENCES posts (id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_images_pest FOREIGN KEY (pest_id) REFERENCES pests (id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_images_uploader FOREIGN KEY (uploaded_by) REFERENCES users (id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Anh gan voi bai viet hoac muc sau benh; PHP dam bao chi chon mot doi tuong';

-- Du lieu danh muc nen. Dung INSERT IGNORE de import lai khong trung ban ghi.
INSERT IGNORE INTO roles (name, description) VALUES
  ('admin',  'Quan tri vien: toan quyen, bao gom quan ly tai khoan'),
  ('editor', 'Bien tap vien: quan ly bai viet va thong tin sau benh');

INSERT IGNORE INTO categories (name, slug, sort_order) VALUES
  ('Giới thiệu cây cà chua và các giống phổ biến', 'gioi-thieu-giong', 1),
  ('Chuẩn bị đất và làm đất', 'chuan-bi-dat', 2),
  ('Gieo hạt và ươm cây con', 'gieo-hat-uom-cay', 3),
  ('Trồng, làm giàn, tỉa cành', 'trong-lam-gian-tia-canh', 4),
  ('Tưới nước và bón phân', 'tuoi-nuoc-bon-phan', 5),
  ('Sâu bệnh hại và cách phòng trừ', 'sau-benh-hai', 6),
  ('Thu hoạch và bảo quản', 'thu-hoach-bao-quan', 7);

INSERT IGNORE INTO growth_stages (name, slug, sort_order) VALUES
  ('Chuẩn bị đất', 'chuan-bi-dat', 1),
  ('Gieo hạt, ươm cây con', 'gieo-uom', 2),
  ('Cây con sau trồng', 'cay-con', 3),
  ('Sinh trưởng thân lá', 'sinh-truong', 4),
  ('Ra hoa, đậu quả', 'ra-hoa-dau-qua', 5),
  ('Thu hoạch', 'thu-hoach', 6);

INSERT IGNORE INTO symptoms (name, body_part) VALUES
  ('Lá vàng', 'leaf'),
  ('Lá héo rũ', 'leaf'),
  ('Lá xoăn, co rút', 'leaf'),
  ('Đốm nâu trên lá', 'leaf'),
  ('Lớp phấn trắng hoặc mốc xám trên lá', 'leaf'),
  ('Thân thối hoặc có vết nâu', 'stem'),
  ('Hoa rụng, không đậu quả', 'flower'),
  ('Đốm đen hoặc thối ở đáy quả', 'fruit'),
  ('Quả nứt', 'fruit'),
  ('Quả có lỗ đục, bị sâu ăn', 'fruit'),
  ('Rễ thối, cây còi cọc', 'root'),
  ('Cây chậm lớn, còi cọc', 'whole_plant');

-- Tai khoan quan tri duoc tao rieng bang setup_admin.php.
-- Khong luu mat khau thuong trong file SQL.

-- Truy van goi y:
-- 1. Tim sau benh khop voi cac trieu chung da chon:
-- SELECT p.*, COUNT(*) AS matched
-- FROM pests p JOIN pest_symptoms ps ON ps.pest_id = p.id
-- WHERE ps.symptom_id IN (1, 2, 3)
-- GROUP BY p.id ORDER BY matched DESC;
--
-- 2. Danh sach bai viet cong khai theo chuyen muc / giai doan:
-- SELECT * FROM posts WHERE status = 'published'
--   AND (category_id = ? OR ? IS NULL)
--   AND (growth_stage_id = ? OR ? IS NULL)
-- ORDER BY created_at DESC, id DESC LIMIT ? OFFSET ?;
