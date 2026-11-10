SET NAMES utf8mb4;
-- =====================================================================
-- Website "So tay huong dan canh tac ca chua" - CSDL MySQL / MariaDB
-- 9 bang: users, roles, categories, posts, growth_stages, pests,
--         symptoms, pest_symptoms, images
-- Chu so huu: Trung. Muon sua cau truc bang phai bao Trung.
-- Quy uoc: ten bang, cot tieng Anh, snake_case; bang ma hoa utf8mb4.
-- =====================================================================
CREATE DATABASE IF NOT EXISTS tomato_guide
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tomato_guide;

-- 1. roles: vai tro nguoi dung, phuc vu phan quyen ---------------------
CREATE TABLE roles (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(30)  NOT NULL UNIQUE COMMENT 'admin, editor',
  description VARCHAR(255) NULL
) ENGINE=InnoDB;

-- 2. users: tai khoan quan tri va bien tap vien ------------------------
CREATE TABLE users (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  role_id       INT UNSIGNED NOT NULL,
  username      VARCHAR(50)  NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL COMMENT 'password_hash() cua PHP',
  full_name     VARCHAR(100) NOT NULL,
  email         VARCHAR(120) NULL UNIQUE,
  is_active     TINYINT(1)   NOT NULL DEFAULT 1,
  created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 3. categories: chuyen muc bai viet -----------------------------------
CREATE TABLE categories (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(100) NOT NULL,
  slug        VARCHAR(120) NOT NULL UNIQUE,
  description TEXT NULL,
  sort_order  INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- 5. growth_stages: cac giai doan sinh truong cua cay ca chua ----------
CREATE TABLE growth_stages (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(100) NOT NULL,
  slug        VARCHAR(120) NOT NULL UNIQUE,
  description TEXT NULL,
  sort_order  INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- 4. posts: bai viet huong dan canh tac --------------------------------
CREATE TABLE posts (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id     INT UNSIGNED NOT NULL,
  growth_stage_id INT UNSIGNED NULL COMMENT 'NULL = bai viet chung',
  author_id       INT UNSIGNED NULL,
  title           VARCHAR(200) NOT NULL,
  slug            VARCHAR(220) NOT NULL UNIQUE,
  summary         VARCHAR(500) NULL,
  content         MEDIUMTEXT   NOT NULL,
  thumbnail       VARCHAR(255) NULL,
  source          VARCHAR(255) NULL COMMENT 'Nguon tham khao',
  status          ENUM('draft','published') NOT NULL DEFAULT 'draft',
  views           INT UNSIGNED NOT NULL DEFAULT 0,
  created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id)     REFERENCES categories(id)    ON DELETE RESTRICT,
  FOREIGN KEY (growth_stage_id) REFERENCES growth_stages(id) ON DELETE SET NULL,
  FOREIGN KEY (author_id)       REFERENCES users(id)         ON DELETE SET NULL,
  KEY idx_posts_status_created (status, created_at),
  FULLTEXT KEY ft_posts (title, summary, content)
) ENGINE=InnoDB;

-- 6. pests: sau benh, nguyen nhan, bien phap phong tru ------------------
CREATE TABLE pests (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(150) NOT NULL,
  slug        VARCHAR(170) NOT NULL UNIQUE,
  type        ENUM('pest','disease') NOT NULL COMMENT 'pest = sau hai, disease = benh',
  description TEXT NULL,
  cause       TEXT NULL,
  prevention  TEXT NULL COMMENT 'Bien phap phong',
  treatment   TEXT NULL COMMENT 'Bien phap tru',
  source      VARCHAR(255) NULL COMMENT 'Nguon tham khao',
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FULLTEXT KEY ft_pests (name, description)
) ENGINE=InnoDB;

-- 7. symptoms: danh sach trieu chung thuong gap tren cay ---------------
CREATE TABLE symptoms (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(150) NOT NULL UNIQUE,
  body_part   ENUM('leaf','stem','flower','fruit','root','whole_plant') NOT NULL DEFAULT 'leaf'
              COMMENT 'Bo phan bieu hien, dung de loc khi tra cuu',
  description TEXT NULL
) ENGINE=InnoDB;

-- 8. pest_symptoms: lien ket sau benh voi trieu chung (n-n) -------------
CREATE TABLE pest_symptoms (
  pest_id    INT UNSIGNED NOT NULL,
  symptom_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (pest_id, symptom_id),
  KEY idx_ps_symptom (symptom_id),
  FOREIGN KEY (pest_id)    REFERENCES pests(id)    ON DELETE CASCADE,
  FOREIGN KEY (symptom_id) REFERENCES symptoms(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 9. images: anh tai len, gan voi bai viet HOAC sau benh ----------------
-- Quy tac (kiem tra bang PHP, khong dung CHECK vi MySQL cam CHECK tren
-- cot co ON DELETE): moi anh chi gan voi 1 trong 2: post_id hoac pest_id.
CREATE TABLE images (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  file_path   VARCHAR(255) NOT NULL COMMENT 'Duong dan tuong doi trong uploads/',
  alt_text    VARCHAR(200) NULL,
  post_id     INT UNSIGNED NULL,
  pest_id     INT UNSIGNED NULL,
  uploaded_by INT UNSIGNED NULL,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (post_id)     REFERENCES posts(id)  ON DELETE CASCADE,
  FOREIGN KEY (pest_id)     REFERENCES pests(id)  ON DELETE CASCADE,
  FOREIGN KEY (uploaded_by) REFERENCES users(id)  ON DELETE SET NULL
) ENGINE=InnoDB;

-- =====================================================================
-- DU LIEU KHOI TAO (Giang co the chinh ten cho khop tai lieu)
-- =====================================================================
INSERT INTO roles (name, description) VALUES
('admin',  'Quan tri vien: toan quyen, quan ly tai khoan'),
('editor', 'Bien tap vien: them, sua bai viet va sau benh');

INSERT INTO categories (name, slug, sort_order) VALUES
('Giới thiệu cây cà chua và các giống phổ biến','gioi-thieu-giong',1),
('Chuẩn bị đất và làm đất','chuan-bi-dat',2),
('Gieo hạt và ươm cây con','gieo-hat-uom-cay',3),
('Trồng, làm giàn, tỉa cành','trong-lam-gian-tia-canh',4),
('Tưới nước và bón phân','tuoi-nuoc-bon-phan',5),
('Sâu bệnh hại và cách phòng trừ','sau-benh-hai',6),
('Thu hoạch và bảo quản','thu-hoach-bao-quan',7);

INSERT INTO growth_stages (name, slug, sort_order) VALUES
('Chuẩn bị đất','chuan-bi-dat',1),
('Gieo hạt, ươm cây con','gieo-uom',2),
('Cây con sau trồng','cay-con',3),
('Sinh trưởng thân lá','sinh-truong',4),
('Ra hoa, đậu quả','ra-hoa-dau-qua',5),
('Thu hoạch','thu-hoach',6);

-- Mot so trieu chung pho bien de tra cuu (danh sach mau, co the them/bot)
INSERT INTO symptoms (name, body_part) VALUES
('Lá vàng','leaf'),
('Lá héo rũ','leaf'),
('Lá xoăn, co rút','leaf'),
('Đốm nâu trên lá','leaf'),
('Lớp phấn trắng hoặc mốc xám trên lá','leaf'),
('Thân thối hoặc có vết nâu','stem'),
('Hoa rụng, không đậu quả','flower'),
('Đốm đen hoặc thối ở đáy quả','fruit'),
('Quả nứt','fruit'),
('Quả có lỗ đục, bị sâu ăn','fruit'),
('Rễ thối, cây còi cọc','root'),
('Cây chậm lớn, còi cọc','whole_plant');
-- Tai khoan admin: chay setup_admin.php (khong luu mat khau trong file SQL)

-- =====================================================================
-- TRUY VAN MAU (cho Quang Anh, Dung)
-- Tra cuu sau benh theo cac trieu chung da chon (xep theo so trieu chung khop):
--   SELECT p.*, COUNT(*) AS matched
--   FROM pests p JOIN pest_symptoms ps ON ps.pest_id = p.id
--   WHERE ps.symptom_id IN (?, ?, ?)
--   GROUP BY p.id ORDER BY matched DESC;
-- Loc bai viet theo chuyen muc va giai doan, co phan trang:
--   SELECT * FROM posts WHERE status='published'
--     AND (category_id = ? OR ? IS NULL) AND (growth_stage_id = ? OR ? IS NULL)
--   ORDER BY created_at DESC LIMIT ? OFFSET ?;
-- =====================================================================
