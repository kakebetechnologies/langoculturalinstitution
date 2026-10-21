-- Lango Cultural Institution (LCI) - database schema
-- Shared by the website, the admin control panel and (later) the Lango mobile app / API.
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('super_admin','admin','editor','moderator','clan_leader','contributor') NOT NULL DEFAULT 'contributor',
  clan_id INT UNSIGNED NULL,
  phone VARCHAR(40) NULL,
  country VARCHAR(80) NULL,
  points INT UNSIGNED NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  must_change_password TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_login DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS settings (
  k VARCHAR(80) PRIMARY KEY,
  v TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- UI strings: k = English text, lg = Leb Lango (entered/approved by language experts)
CREATE TABLE IF NOT EXISTS translations (
  h CHAR(32) PRIMARY KEY,
  k TEXT NOT NULL,
  lg TEXT NULL,
  updated_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS audit_log (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  action VARCHAR(40) NOT NULL,
  entity VARCHAR(60) NULL,
  entity_id INT UNSIGNED NULL,
  detail VARCHAR(500) NULL,
  ip VARCHAR(45) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY (entity, entity_id), KEY (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Version control: every edit in the admin panel stores the previous row
CREATE TABLE IF NOT EXISTS revisions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  entity VARCHAR(60) NOT NULL,
  entity_id INT UNSIGNED NOT NULL,
  data MEDIUMTEXT NOT NULL,
  user_id INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY (entity, entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS districts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(120) NOT NULL UNIQUE,
  headquarters VARCHAR(120) NULL,
  description TEXT NULL,
  description_lg TEXT NULL,
  image VARCHAR(255) NULL,
  lat DECIMAL(9,6) NULL,
  lng DECIMAL(9,6) NULL,
  sort INT NOT NULL DEFAULT 0,
  status ENUM('published','draft') NOT NULL DEFAULT 'published'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS clans (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  slug VARCHAR(170) NOT NULL UNIQUE,
  totem VARCHAR(150) NULL,
  totem_type ENUM('animal','plant','bird','other') NULL,
  district_id INT UNSIGNED NULL,
  summary VARCHAR(400) NULL,
  history MEDIUMTEXT NULL,
  history_lg MEDIUMTEXT NULL,
  lineage MEDIUMTEXT NULL,
  leader_name VARCHAR(150) NULL,
  leader_title VARCHAR(80) NULL DEFAULT 'Awitong',
  leader_photo VARCHAR(255) NULL,
  notable_members TEXT NULL,
  image VARCHAR(255) NULL,
  verified TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('published','draft') NOT NULL DEFAULT 'draft',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  KEY (district_id), KEY (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS heritage_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category ENUM('ceremony','attire','craft','music','dance','storytelling','symbol','site') NOT NULL,
  title VARCHAR(190) NOT NULL,
  title_lg VARCHAR(190) NULL,
  slug VARCHAR(210) NOT NULL UNIQUE,
  summary VARCHAR(500) NULL,
  body MEDIUMTEXT NULL,
  body_lg MEDIUMTEXT NULL,
  image VARCHAR(255) NULL,
  video_url VARCHAR(255) NULL,
  audio VARCHAR(255) NULL,
  clan_id INT UNSIGNED NULL,
  district_id INT UNSIGNED NULL,
  lat DECIMAL(9,6) NULL,
  lng DECIMAL(9,6) NULL,
  featured TINYINT(1) NOT NULL DEFAULT 0,
  verified TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('published','draft') NOT NULL DEFAULT 'draft',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  KEY (category), KEY (district_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS species (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  name_lg VARCHAR(150) NULL,
  slug VARCHAR(170) NOT NULL UNIQUE,
  scientific_name VARCHAR(190) NULL,
  kind ENUM('plant','animal','bird','insect','other') NOT NULL DEFAULT 'animal',
  conservation ENUM('unknown','least_concern','vulnerable','endangered','critical','locally_extinct','reintroduced') NOT NULL DEFAULT 'unknown',
  medicinal TINYINT(1) NOT NULL DEFAULT 0,
  uses TEXT NULL,
  habitat TEXT NULL,
  body MEDIUMTEXT NULL,
  image VARCHAR(255) NULL,
  status ENUM('published','draft') NOT NULL DEFAULT 'draft',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS post_categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(120) NOT NULL UNIQUE,
  sort INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS posts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id INT UNSIGNED NULL,
  title VARCHAR(220) NOT NULL,
  title_lg VARCHAR(220) NULL,
  slug VARCHAR(240) NOT NULL UNIQUE,
  excerpt VARCHAR(500) NULL,
  excerpt_lg VARCHAR(500) NULL,
  body MEDIUMTEXT NULL,
  body_lg MEDIUMTEXT NULL,
  image VARCHAR(255) NULL,
  video_url VARCHAR(255) NULL,
  audio VARCHAR(255) NULL,
  author_name VARCHAR(120) NULL,
  featured TINYINT(1) NOT NULL DEFAULT 0,
  views INT UNSIGNED NOT NULL DEFAULT 0,
  status ENUM('published','draft') NOT NULL DEFAULT 'draft',
  published_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY (category_id), KEY (status, published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS comments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  post_id INT UNSIGNED NOT NULL,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NULL,
  body TEXT NOT NULL,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY (post_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS subscribers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL UNIQUE,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS events (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  title_lg VARCHAR(200) NULL,
  slug VARCHAR(220) NOT NULL UNIQUE,
  summary VARCHAR(500) NULL,
  body MEDIUMTEXT NULL,
  body_lg MEDIUMTEXT NULL,
  image VARCHAR(255) NULL,
  location VARCHAR(190) NULL,
  starts_at DATETIME NOT NULL,
  ends_at DATETIME NULL,
  capacity INT UNSIGNED NULL,
  registration_open TINYINT(1) NOT NULL DEFAULT 1,
  featured TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('published','draft') NOT NULL DEFAULT 'draft',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY (starts_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS event_registrations (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  event_id INT UNSIGNED NOT NULL,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(40) NULL,
  tickets TINYINT UNSIGNED NOT NULL DEFAULT 1,
  code VARCHAR(16) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY (event_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS programs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  title_lg VARCHAR(200) NULL,
  slug VARCHAR(220) NOT NULL UNIQUE,
  category ENUM('heritage','education','youth','conservation','development','digital') NOT NULL DEFAULT 'heritage',
  summary VARCHAR(500) NULL,
  body MEDIUMTEXT NULL,
  body_lg MEDIUMTEXT NULL,
  image VARCHAR(255) NULL,
  goal_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  raised_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  progress TINYINT UNSIGNED NOT NULL DEFAULT 0,
  accepts_donations TINYINT(1) NOT NULL DEFAULT 1,
  featured TINYINT(1) NOT NULL DEFAULT 0,
  sort INT NOT NULL DEFAULT 0,
  status ENUM('published','draft') NOT NULL DEFAULT 'draft',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS leaders (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  grp ENUM('won_nyaci','elders','cabinet','awitong') NOT NULL DEFAULT 'cabinet',
  name VARCHAR(150) NOT NULL,
  title VARCHAR(150) NULL,
  bio TEXT NULL,
  photo VARCHAR(255) NULL,
  clan_id INT UNSIGNED NULL,
  sort INT NOT NULL DEFAULT 0,
  status ENUM('published','draft') NOT NULL DEFAULT 'published'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS timeline (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  year VARCHAR(20) NOT NULL,
  title VARCHAR(190) NOT NULL,
  body TEXT NULL,
  sort INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS documents (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(220) NOT NULL,
  category ENUM('policy','governance','historical','research','report','other') NOT NULL DEFAULT 'other',
  description TEXT NULL,
  file VARCHAR(255) NULL,
  year SMALLINT UNSIGNED NULL,
  downloads INT UNSIGNED NOT NULL DEFAULT 0,
  status ENUM('published','draft') NOT NULL DEFAULT 'draft',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS media (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  type ENUM('photo','video','audio','podcast') NOT NULL DEFAULT 'photo',
  title VARCHAR(200) NOT NULL,
  description TEXT NULL,
  file VARCHAR(255) NULL,
  url VARCHAR(255) NULL,
  album VARCHAR(120) NULL,
  credit VARCHAR(150) NULL,
  source ENUM('lci','community') NOT NULL DEFAULT 'lci',
  status ENUM('published','draft') NOT NULL DEFAULT 'draft',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY (type, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS dictionary (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  lango VARCHAR(190) NOT NULL,
  english VARCHAR(500) NOT NULL,
  pos VARCHAR(40) NULL,
  example_lg VARCHAR(500) NULL,
  example_en VARCHAR(500) NULL,
  audio VARCHAR(255) NULL,
  source VARCHAR(190) NULL,
  verified TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('published','draft') NOT NULL DEFAULT 'published',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY (lango), KEY (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS proverbs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  lango TEXT NOT NULL,
  english TEXT NULL,
  meaning TEXT NULL,
  clan_id INT UNSIGNED NULL,
  verified TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('published','draft') NOT NULL DEFAULT 'draft',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS lessons (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  level ENUM('beginner','intermediate','advanced') NOT NULL DEFAULT 'beginner',
  title VARCHAR(190) NOT NULL,
  body MEDIUMTEXT NULL,
  audio VARCHAR(255) NULL,
  sort INT NOT NULL DEFAULT 0,
  status ENUM('published','draft') NOT NULL DEFAULT 'draft'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS knowledge (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  topic VARCHAR(120) NULL,
  question VARCHAR(300) NOT NULL,
  answer TEXT NOT NULL,
  reviewed TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('published','draft') NOT NULL DEFAULT 'published'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS chat_log (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  question VARCHAR(500) NOT NULL,
  answer TEXT NULL,
  flagged TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS contributions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(16) NOT NULL UNIQUE,
  user_id INT UNSIGNED NULL,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(190) NULL,
  phone VARCHAR(40) NULL,
  type ENUM('cultural','natural','language','oral_history','document','heritage_site') NOT NULL,
  title VARCHAR(220) NOT NULL,
  description TEXT NULL,
  clan_id INT UNSIGNED NULL,
  district_id INT UNSIGNED NULL,
  location_text VARCHAR(190) NULL,
  lat DECIMAL(9,6) NULL,
  lng DECIMAL(9,6) NULL,
  observed_on DATE NULL,
  media_file VARCHAR(255) NULL,
  media_kind ENUM('none','image','audio','video','document') NOT NULL DEFAULT 'none',
  rights ENUM('retain','cc_by','lci') NOT NULL DEFAULT 'retain',
  status ENUM('pending','under_review','needs_info','approved','published','rejected') NOT NULL DEFAULT 'pending',
  reviewer_id INT UNSIGNED NULL,
  review_note TEXT NULL,
  points_awarded INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  KEY (status), KEY (type), KEY (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS donations (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reference VARCHAR(20) NOT NULL UNIQUE,
  donor_name VARCHAR(150) NOT NULL,
  email VARCHAR(190) NULL,
  phone VARCHAR(40) NULL,
  country VARCHAR(80) NULL,
  amount DECIMAL(14,2) NOT NULL,
  currency CHAR(3) NOT NULL DEFAULT 'UGX',
  method ENUM('mtn_momo','airtel_money','visa','mastercard','paypal','bank','cash') NOT NULL DEFAULT 'mtn_momo',
  program_id INT UNSIGNED NULL,
  recurring ENUM('once','monthly','yearly') NOT NULL DEFAULT 'once',
  anonymous TINYINT(1) NOT NULL DEFAULT 0,
  message VARCHAR(500) NULL,
  status ENUM('pledged','confirmed','failed') NOT NULL DEFAULT 'pledged',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY (status), KEY (program_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS applications (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  type ENUM('scholarship','grant','volunteer','diaspora','partner','clan') NOT NULL,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(40) NULL,
  organization VARCHAR(190) NULL,
  country VARCHAR(80) NULL,
  details TEXT NULL,
  status ENUM('new','reviewing','accepted','declined') NOT NULL DEFAULT 'new',
  notes TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY (type, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS messages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(40) NULL,
  subject VARCHAR(190) NULL,
  body TEXT NOT NULL,
  status ENUM('new','read','replied') NOT NULL DEFAULT 'new',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS professionals (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(190) NULL,
  phone VARCHAR(40) NULL,
  clan_id INT UNSIGNED NULL,
  industry VARCHAR(120) NOT NULL,
  expertise VARCHAR(250) NULL,
  location VARCHAR(150) NULL,
  bio TEXT NULL,
  photo VARCHAR(255) NULL,
  status ENUM('pending','published','rejected') NOT NULL DEFAULT 'pending',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS partners (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  logo VARCHAR(255) NULL,
  url VARCHAR(255) NULL,
  category VARCHAR(80) NULL,
  sort INT NOT NULL DEFAULT 0,
  status ENUM('published','draft') NOT NULL DEFAULT 'published'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tours (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(190) NOT NULL,
  description TEXT NULL,
  panorama VARCHAR(255) NULL,
  location VARCHAR(190) NULL,
  sort INT NOT NULL DEFAULT 0,
  status ENUM('published','draft') NOT NULL DEFAULT 'draft'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
