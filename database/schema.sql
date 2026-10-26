-- =====================================================================
-- BK Enfab website + CMS + Lead CRM
-- MySQL 8.x / MariaDB 10.6+ schema  (InnoDB, utf8mb4)
-- Version: 1 (matches application/migrations/001_initial_schema.php)
-- =====================================================================
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- Users, roles, permissions
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS roles (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name          VARCHAR(100) NOT NULL,
  slug          VARCHAR(100) NOT NULL,
  description   VARCHAR(255) NULL,
  is_super      TINYINT(1) NOT NULL DEFAULT 0,
  is_system     TINYINT(1) NOT NULL DEFAULT 0,
  created_at    DATETIME NULL,
  updated_at    DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_roles_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS permissions (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  module        VARCHAR(60) NOT NULL,
  action        VARCHAR(40) NOT NULL,
  label         VARCHAR(150) NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_perm (module, action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS role_permissions (
  role_id       INT UNSIGNED NOT NULL,
  permission_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (role_id, permission_id),
  KEY idx_rp_perm (permission_id),
  CONSTRAINT fk_rp_role FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE,
  CONSTRAINT fk_rp_perm FOREIGN KEY (permission_id) REFERENCES permissions (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
  id                    INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name                  VARCHAR(150) NOT NULL,
  email                 VARCHAR(190) NOT NULL,
  phone                 VARCHAR(30) NULL,
  password_hash         VARCHAR(255) NOT NULL,
  role_id               INT UNSIGNED NOT NULL,
  status                ENUM('active','inactive') NOT NULL DEFAULT 'active',
  last_login            DATETIME NULL,
  last_login_ip         VARCHAR(45) NULL,
  failed_attempts       SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  locked_until          DATETIME NULL,
  reset_token_hash      CHAR(64) NULL,
  reset_expires_at      DATETIME NULL,
  twofa_secret          VARCHAR(64) NULL,
  twofa_enabled         TINYINT(1) NOT NULL DEFAULT 0,
  password_changed_at   DATETIME NULL,
  created_at            DATETIME NULL,
  updated_at            DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email),
  KEY idx_users_role (role_id),
  KEY idx_users_reset (reset_token_hash),
  CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Generic throttle table: login attempts, public form submissions
CREATE TABLE IF NOT EXISTS throttle (
  id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  bucket        VARCHAR(40) NOT NULL,
  ip_address    VARCHAR(45) NOT NULL,
  identifier    VARCHAR(190) NULL,
  created_at    DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_throttle_lookup (bucket, ip_address, created_at),
  KEY idx_throttle_ident (bucket, identifier, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS activity_logs (
  id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id       INT UNSIGNED NULL,
  action        VARCHAR(40) NOT NULL,
  module        VARCHAR(60) NOT NULL,
  record_id     BIGINT UNSIGNED NULL,
  description   VARCHAR(500) NULL,
  ip_address    VARCHAR(45) NULL,
  user_agent    VARCHAR(255) NULL,
  created_at    DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_logs_user (user_id),
  KEY idx_logs_module (module, record_id),
  KEY idx_logs_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Settings, offices, business hours, email templates
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS settings (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `key`         VARCHAR(100) NOT NULL,
  `value`       MEDIUMTEXT NULL,
  `group`       VARCHAR(40) NOT NULL DEFAULT 'general',
  `type`        VARCHAR(150) NOT NULL DEFAULT 'text',
  label         VARCHAR(150) NULL,
  help          VARCHAR(255) NULL,
  is_protected  TINYINT(1) NOT NULL DEFAULT 0,
  sort_order    INT NOT NULL DEFAULT 0,
  updated_at    DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_settings_key (`key`),
  KEY idx_settings_group (`group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS offices (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name          VARCHAR(150) NOT NULL,
  office_type   ENUM('head_office','branch','factory','site_office') NOT NULL DEFAULT 'branch',
  address       TEXT NULL,
  city          VARCHAR(100) NULL,
  state         VARCHAR(100) NULL,
  pincode       VARCHAR(20) NULL,
  country       VARCHAR(100) NULL,
  phone         VARCHAR(60) NULL,
  email         VARCHAR(190) NULL,
  map_url       VARCHAR(500) NULL,
  latitude      DECIMAL(10,7) NULL,
  longitude     DECIMAL(10,7) NULL,
  is_primary    TINYINT(1) NOT NULL DEFAULT 0,
  sort_order    INT NOT NULL DEFAULT 0,
  status        ENUM('draft','published') NOT NULL DEFAULT 'published',
  created_at    DATETIME NULL,
  updated_at    DATETIME NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS business_hours (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  day_of_week   TINYINT UNSIGNED NOT NULL COMMENT '1=Mon .. 7=Sun',
  open_time     TIME NULL,
  close_time    TIME NULL,
  is_closed     TINYINT(1) NOT NULL DEFAULT 0,
  note          VARCHAR(150) NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_bh_day (day_of_week)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS email_templates (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug          VARCHAR(80) NOT NULL,
  name          VARCHAR(150) NOT NULL,
  subject       VARCHAR(255) NOT NULL,
  body          MEDIUMTEXT NOT NULL,
  variables     VARCHAR(500) NULL,
  is_active     TINYINT(1) NOT NULL DEFAULT 1,
  updated_at    DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_et_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Navigation
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS menus (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name          VARCHAR(100) NOT NULL,
  location      VARCHAR(50) NOT NULL,
  status        ENUM('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (id),
  UNIQUE KEY uq_menus_location (location)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS menu_items (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  menu_id       INT UNSIGNED NOT NULL,
  parent_id     INT UNSIGNED NULL,
  title         VARCHAR(120) NOT NULL,
  url           VARCHAR(500) NULL,
  page_id       INT UNSIGNED NULL,
  target        ENUM('_self','_blank') NOT NULL DEFAULT '_self',
  css_class     VARCHAR(60) NULL,
  sort_order    INT NOT NULL DEFAULT 0,
  is_active     TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  KEY idx_mi_menu (menu_id, parent_id, sort_order),
  CONSTRAINT fk_mi_menu FOREIGN KEY (menu_id) REFERENCES menus (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Pages and structured sections (page builder / homepage sections)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pages (
  id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title             VARCHAR(200) NOT NULL,
  slug              VARCHAR(200) NOT NULL,
  template          VARCHAR(40) NOT NULL DEFAULT 'default',
  h1                VARCHAR(200) NULL,
  excerpt           TEXT NULL,
  content           MEDIUMTEXT NULL,
  banner_image      VARCHAR(255) NULL,
  status            ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
  is_system         TINYINT(1) NOT NULL DEFAULT 0,
  meta_title        VARCHAR(255) NULL,
  meta_description  VARCHAR(500) NULL,
  focus_keyword     VARCHAR(150) NULL,
  canonical_url     VARCHAR(500) NULL,
  og_image          VARCHAR(255) NULL,
  meta_robots       VARCHAR(40) NOT NULL DEFAULT 'index,follow',
  published_at      DATETIME NULL,
  created_by        INT UNSIGNED NULL,
  updated_by        INT UNSIGNED NULL,
  created_at        DATETIME NULL,
  updated_at        DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_pages_slug (slug),
  KEY idx_pages_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- owner_type: 'home' (owner_id 0) or 'page' (owner_id = pages.id)
CREATE TABLE IF NOT EXISTS page_sections (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  owner_type    VARCHAR(20) NOT NULL DEFAULT 'home',
  owner_id      INT UNSIGNED NOT NULL DEFAULT 0,
  section_type  VARCHAR(40) NOT NULL,
  title         VARCHAR(255) NULL,
  subtitle      VARCHAR(500) NULL,
  content       MEDIUMTEXT NULL,
  image         VARCHAR(255) NULL,
  image_alt     VARCHAR(255) NULL,
  button_text   VARCHAR(80) NULL,
  button_url    VARCHAR(500) NULL,
  settings_json TEXT NULL,
  sort_order    INT NOT NULL DEFAULT 0,
  is_active     TINYINT(1) NOT NULL DEFAULT 1,
  created_at    DATETIME NULL,
  updated_at    DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_ps_owner (owner_type, owner_id, is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS faqs (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  faq_group     VARCHAR(60) NOT NULL DEFAULT 'general',
  question      VARCHAR(500) NOT NULL,
  answer        TEXT NOT NULL,
  sort_order    INT NOT NULL DEFAULT 0,
  status        ENUM('draft','published') NOT NULL DEFAULT 'draft',
  created_at    DATETIME NULL,
  updated_at    DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_faq_group (faq_group, status, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Client / certification / partner logo strip
CREATE TABLE IF NOT EXISTS logos (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  logo_type     ENUM('client','certification','partner','membership') NOT NULL DEFAULT 'client',
  name          VARCHAR(150) NOT NULL,
  image         VARCHAR(255) NULL,
  url           VARCHAR(500) NULL,
  sort_order    INT NOT NULL DEFAULT 0,
  status        ENUM('draft','published') NOT NULL DEFAULT 'draft',
  created_at    DATETIME NULL,
  updated_at    DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_logos_type (logo_type, status, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Services, roofing, industries
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS services (
  id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  parent_id         INT UNSIGNED NULL,
  title             VARCHAR(200) NOT NULL,
  slug              VARCHAR(200) NOT NULL,
  short_description TEXT NULL,
  description       MEDIUMTEXT NULL,
  icon              VARCHAR(80) NULL,
  image             VARCHAR(255) NULL,
  image_alt         VARCHAR(255) NULL,
  is_featured       TINYINT(1) NOT NULL DEFAULT 0,
  sort_order        INT NOT NULL DEFAULT 0,
  status            ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
  meta_title        VARCHAR(255) NULL,
  meta_description  VARCHAR(500) NULL,
  focus_keyword     VARCHAR(150) NULL,
  canonical_url     VARCHAR(500) NULL,
  og_image          VARCHAR(255) NULL,
  meta_robots       VARCHAR(40) NOT NULL DEFAULT 'index,follow',
  created_at        DATETIME NULL,
  updated_at        DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_services_slug (slug),
  KEY idx_services_status (status, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS roofing_categories (
  id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  parent_id         INT UNSIGNED NULL,
  name              VARCHAR(200) NOT NULL,
  slug              VARCHAR(200) NOT NULL,
  system_name       VARCHAR(200) NULL,
  short_description TEXT NULL,
  description       MEDIUMTEXT NULL,
  key_benefits      TEXT NULL,
  image             VARCHAR(255) NULL,
  image_alt         VARCHAR(255) NULL,
  sort_order        INT NOT NULL DEFAULT 0,
  status            ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
  meta_title        VARCHAR(255) NULL,
  meta_description  VARCHAR(500) NULL,
  focus_keyword     VARCHAR(150) NULL,
  canonical_url     VARCHAR(500) NULL,
  og_image          VARCHAR(255) NULL,
  meta_robots       VARCHAR(40) NOT NULL DEFAULT 'index,follow',
  created_at        DATETIME NULL,
  updated_at        DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_rc_slug (slug),
  KEY idx_rc_status (status, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS industries (
  id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name              VARCHAR(200) NOT NULL,
  slug              VARCHAR(200) NOT NULL,
  short_description TEXT NULL,
  description       MEDIUMTEXT NULL,
  icon              VARCHAR(80) NULL,
  image             VARCHAR(255) NULL,
  image_alt         VARCHAR(255) NULL,
  sort_order        INT NOT NULL DEFAULT 0,
  status            ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
  meta_title        VARCHAR(255) NULL,
  meta_description  VARCHAR(500) NULL,
  focus_keyword     VARCHAR(150) NULL,
  canonical_url     VARCHAR(500) NULL,
  og_image          VARCHAR(255) NULL,
  meta_robots       VARCHAR(40) NOT NULL DEFAULT 'index,follow',
  created_at        DATETIME NULL,
  updated_at        DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_ind_slug (slug),
  KEY idx_ind_status (status, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Projects (general + roofing projects share this table)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS project_categories (
  id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  parent_id         INT UNSIGNED NULL,
  name              VARCHAR(150) NOT NULL,
  slug              VARCHAR(150) NOT NULL,
  description       TEXT NULL,
  sort_order        INT NOT NULL DEFAULT 0,
  status            ENUM('draft','published') NOT NULL DEFAULT 'published',
  meta_title        VARCHAR(255) NULL,
  meta_description  VARCHAR(500) NULL,
  created_at        DATETIME NULL,
  updated_at        DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_pc_slug (slug),
  KEY idx_pc_parent (parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS projects (
  id                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title               VARCHAR(200) NOT NULL,
  slug                VARCHAR(200) NOT NULL,
  category_id         INT UNSIGNED NULL,
  subcategory_id      INT UNSIGNED NULL,
  service_id          INT UNSIGNED NULL,
  roofing_category_id INT UNSIGNED NULL,
  industry_id         INT UNSIGNED NULL,
  project_type        VARCHAR(120) NULL,
  roofing_system_name VARCHAR(200) NULL,
  client_name         VARCHAR(200) NULL,
  show_client         TINYINT(1) NOT NULL DEFAULT 0,
  city                VARCHAR(100) NULL,
  state               VARCHAR(100) NULL,
  country             VARCHAR(100) NULL,
  project_year        SMALLINT UNSIGNED NULL,
  completion_date     DATE NULL,
  project_status      ENUM('completed','ongoing','upcoming') NOT NULL DEFAULT 'completed',
  roof_area           VARCHAR(60) NULL,
  roof_material       VARCHAR(200) NULL,
  roof_profile        VARCHAR(200) NULL,
  structure_type      VARCHAR(200) NULL,
  summary             TEXT NULL,
  scope               MEDIUMTEXT NULL,
  content             MEDIUMTEXT NULL,
  challenges          MEDIUMTEXT NULL,
  solution            MEDIUMTEXT NULL,
  specifications      MEDIUMTEXT NULL,
  benefits            MEDIUMTEXT NULL,
  result              MEDIUMTEXT NULL,
  video_url           VARCHAR(500) NULL,
  document_file       VARCHAR(255) NULL,
  cover_image         VARCHAR(255) NULL,
  cover_alt           VARCHAR(255) NULL,
  is_featured         TINYINT(1) NOT NULL DEFAULT 0,
  sort_order          INT NOT NULL DEFAULT 0,
  status              ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
  meta_title          VARCHAR(255) NULL,
  meta_description    VARCHAR(500) NULL,
  focus_keyword       VARCHAR(150) NULL,
  canonical_url       VARCHAR(500) NULL,
  og_image            VARCHAR(255) NULL,
  meta_robots         VARCHAR(40) NOT NULL DEFAULT 'index,follow',
  published_at        DATETIME NULL,
  created_by          INT UNSIGNED NULL,
  updated_by          INT UNSIGNED NULL,
  created_at          DATETIME NULL,
  updated_at          DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_projects_slug (slug),
  KEY idx_projects_list (status, is_featured, sort_order),
  KEY idx_projects_cat (category_id),
  KEY idx_projects_roof (roofing_category_id),
  KEY idx_projects_service (service_id),
  KEY idx_projects_industry (industry_id),
  KEY idx_projects_year (project_year),
  KEY idx_projects_pstatus (project_status),
  KEY idx_projects_city (city)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS project_images (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  project_id    INT UNSIGNED NOT NULL,
  image         VARCHAR(255) NOT NULL,
  thumb         VARCHAR(255) NULL,
  webp          VARCHAR(255) NULL,
  alt           VARCHAR(255) NULL,
  caption       VARCHAR(500) NULL,
  image_type    ENUM('gallery','before','after') NOT NULL DEFAULT 'gallery',
  sort_order    INT NOT NULL DEFAULT 0,
  is_cover      TINYINT(1) NOT NULL DEFAULT 0,
  created_at    DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_pi_project (project_id, image_type, sort_order),
  CONSTRAINT fk_pi_project FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS project_related (
  project_id    INT UNSIGNED NOT NULL,
  related_id    INT UNSIGNED NOT NULL,
  PRIMARY KEY (project_id, related_id),
  KEY idx_pr_related (related_id),
  CONSTRAINT fk_pr_project FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE,
  CONSTRAINT fk_pr_related FOREIGN KEY (related_id) REFERENCES projects (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Gallery, testimonials, media
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS gallery_albums (
  id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  category          VARCHAR(100) NULL,
  title             VARCHAR(200) NOT NULL,
  slug              VARCHAR(200) NOT NULL,
  description       TEXT NULL,
  cover_image       VARCHAR(255) NULL,
  sort_order        INT NOT NULL DEFAULT 0,
  status            ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
  meta_title        VARCHAR(255) NULL,
  meta_description  VARCHAR(500) NULL,
  created_at        DATETIME NULL,
  updated_at        DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_ga_slug (slug),
  KEY idx_ga_status (status, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS gallery_items (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  album_id      INT UNSIGNED NOT NULL,
  item_type     ENUM('image','video') NOT NULL DEFAULT 'image',
  image         VARCHAR(255) NULL,
  thumb         VARCHAR(255) NULL,
  video_url     VARCHAR(500) NULL,
  alt           VARCHAR(255) NULL,
  caption       VARCHAR(500) NULL,
  sort_order    INT NOT NULL DEFAULT 0,
  created_at    DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_gi_album (album_id, sort_order),
  CONSTRAINT fk_gi_album FOREIGN KEY (album_id) REFERENCES gallery_albums (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS testimonials (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name          VARCHAR(150) NOT NULL,
  company       VARCHAR(150) NULL,
  designation   VARCHAR(150) NULL,
  content       TEXT NOT NULL,
  image         VARCHAR(255) NULL,
  rating        TINYINT UNSIGNED NULL,
  project_id    INT UNSIGNED NULL,
  is_featured   TINYINT(1) NOT NULL DEFAULT 0,
  sort_order    INT NOT NULL DEFAULT 0,
  status        ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
  created_at    DATETIME NULL,
  updated_at    DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_t_status (status, is_featured, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS media (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  folder        VARCHAR(60) NOT NULL DEFAULT 'general',
  original_name VARCHAR(255) NOT NULL,
  filename      VARCHAR(255) NOT NULL,
  path          VARCHAR(255) NOT NULL,
  thumb_path    VARCHAR(255) NULL,
  webp_path     VARCHAR(255) NULL,
  mime          VARCHAR(100) NOT NULL,
  file_type     ENUM('image','document','video','other') NOT NULL DEFAULT 'image',
  size_bytes    INT UNSIGNED NOT NULL DEFAULT 0,
  width         INT UNSIGNED NULL,
  height        INT UNSIGNED NULL,
  alt           VARCHAR(255) NULL,
  caption       VARCHAR(500) NULL,
  metadata      TEXT NULL,
  uploaded_by   INT UNSIGNED NULL,
  created_at    DATETIME NULL,
  updated_at    DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_media_path (path),
  KEY idx_media_folder (folder),
  KEY idx_media_search (original_name, alt)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Blog
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS blog_categories (
  id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name              VARCHAR(150) NOT NULL,
  slug              VARCHAR(150) NOT NULL,
  description       TEXT NULL,
  status            ENUM('draft','published') NOT NULL DEFAULT 'published',
  meta_title        VARCHAR(255) NULL,
  meta_description  VARCHAR(500) NULL,
  created_at        DATETIME NULL,
  updated_at        DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_bc_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS blog_tags (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name          VARCHAR(100) NOT NULL,
  slug          VARCHAR(100) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_bt_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS blog_posts (
  id                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  category_id         INT UNSIGNED NULL,
  author_id           INT UNSIGNED NULL,
  title               VARCHAR(255) NOT NULL,
  slug                VARCHAR(200) NOT NULL,
  excerpt             TEXT NULL,
  content             MEDIUMTEXT NULL,
  featured_image      VARCHAR(255) NULL,
  featured_image_alt  VARCHAR(255) NULL,
  status              ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
  published_at        DATETIME NULL,
  meta_title          VARCHAR(255) NULL,
  meta_description    VARCHAR(500) NULL,
  focus_keyword       VARCHAR(150) NULL,
  canonical_url       VARCHAR(500) NULL,
  og_image            VARCHAR(255) NULL,
  meta_robots         VARCHAR(40) NOT NULL DEFAULT 'index,follow',
  created_at          DATETIME NULL,
  updated_at          DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_bp_slug (slug),
  KEY idx_bp_list (status, published_at),
  KEY idx_bp_cat (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS blog_post_tags (
  post_id       INT UNSIGNED NOT NULL,
  tag_id        INT UNSIGNED NOT NULL,
  PRIMARY KEY (post_id, tag_id),
  KEY idx_bpt_tag (tag_id),
  CONSTRAINT fk_bpt_post FOREIGN KEY (post_id) REFERENCES blog_posts (id) ON DELETE CASCADE,
  CONSTRAINT fk_bpt_tag FOREIGN KEY (tag_id) REFERENCES blog_tags (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Lead CRM
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS leads (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  lead_no         VARCHAR(30) NOT NULL,
  form_type       ENUM('contact','quote','manual') NOT NULL DEFAULT 'contact',
  name            VARCHAR(150) NOT NULL,
  company         VARCHAR(190) NULL,
  email           VARCHAR(190) NULL,
  phone           VARCHAR(30) NULL,
  phone_normalized VARCHAR(20) NULL,
  city            VARCHAR(100) NULL,
  state           VARCHAR(100) NULL,
  service_id      INT UNSIGNED NULL,
  service_name    VARCHAR(200) NULL,
  project_type    VARCHAR(150) NULL,
  project_size    VARCHAR(100) NULL,
  budget          VARCHAR(100) NULL,
  timeline        VARCHAR(100) NULL,
  subject         VARCHAR(255) NULL,
  message         TEXT NULL,
  attachment_path VARCHAR(255) NULL,
  attachment_name VARCHAR(255) NULL,
  source          ENUM('website_contact','quote_form','phone','whatsapp','email','manual','campaign') NOT NULL DEFAULT 'website_contact',
  source_url      VARCHAR(500) NULL,
  referrer        VARCHAR(500) NULL,
  utm_source      VARCHAR(150) NULL,
  utm_medium      VARCHAR(150) NULL,
  utm_campaign    VARCHAR(150) NULL,
  utm_term        VARCHAR(150) NULL,
  utm_content     VARCHAR(150) NULL,
  ip_address      VARCHAR(45) NULL,
  user_agent      VARCHAR(255) NULL,
  consent         TINYINT(1) NOT NULL DEFAULT 0,
  consent_at      DATETIME NULL,
  status          ENUM('new','contacted','qualified','site_visit','quotation_sent','negotiation','won','lost','junk') NOT NULL DEFAULT 'new',
  priority        ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  assigned_to     INT UNSIGNED NULL,
  duplicate_of    INT UNSIGNED NULL,
  last_activity_at DATETIME NULL,
  created_by      INT UNSIGNED NULL,
  created_at      DATETIME NOT NULL,
  updated_at      DATETIME NULL,
  deleted_at      DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_leads_no (lead_no),
  KEY idx_leads_status (status, created_at),
  KEY idx_leads_source (source),
  KEY idx_leads_service (service_id),
  KEY idx_leads_assigned (assigned_to),
  KEY idx_leads_email (email),
  KEY idx_leads_phone (phone_normalized),
  KEY idx_leads_created (created_at),
  KEY idx_leads_utm (utm_source, utm_campaign)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS lead_notes (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  lead_id       INT UNSIGNED NOT NULL,
  user_id       INT UNSIGNED NULL,
  note          TEXT NOT NULL,
  created_at    DATETIME NOT NULL,
  updated_at    DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_ln_lead (lead_id),
  CONSTRAINT fk_ln_lead FOREIGN KEY (lead_id) REFERENCES leads (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS lead_followups (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  lead_id       INT UNSIGNED NOT NULL,
  user_id       INT UNSIGNED NULL,
  followup_at   DATETIME NOT NULL,
  status        ENUM('pending','done','cancelled') NOT NULL DEFAULT 'pending',
  note          VARCHAR(500) NULL,
  reminded_at   DATETIME NULL,
  completed_at  DATETIME NULL,
  created_at    DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_lf_lead (lead_id),
  KEY idx_lf_due (status, followup_at),
  CONSTRAINT fk_lf_lead FOREIGN KEY (lead_id) REFERENCES leads (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS lead_activities (
  id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  lead_id       INT UNSIGNED NOT NULL,
  user_id       INT UNSIGNED NULL,
  activity_type VARCHAR(40) NOT NULL,
  description   VARCHAR(500) NOT NULL,
  created_at    DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_la_lead (lead_id, created_at),
  CONSTRAINT fk_la_lead FOREIGN KEY (lead_id) REFERENCES leads (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- SEO: redirects
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS redirects (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  old_url       VARCHAR(500) NOT NULL,
  old_url_hash  CHAR(40) NOT NULL,
  new_url       VARCHAR(500) NOT NULL,
  status_code   SMALLINT UNSIGNED NOT NULL DEFAULT 301,
  is_active     TINYINT(1) NOT NULL DEFAULT 1,
  origin        ENUM('manual','auto','import') NOT NULL DEFAULT 'manual',
  hits          INT UNSIGNED NOT NULL DEFAULT 0,
  last_hit_at   DATETIME NULL,
  created_at    DATETIME NULL,
  updated_at    DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_redirect_old (old_url_hash),
  KEY idx_redirect_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
