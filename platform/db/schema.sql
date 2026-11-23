-- Wedding Websites & More — schema.
-- MySQL 5.7+ / MariaDB 10.3+, utf8mb4 throughout so Yoruba, Igbo and Hausa diacritics and emoji
-- all survive. InnoDB for real foreign keys. Designed so the WordPress importer can map onto it
-- without reshaping anything afterwards.

SET NAMES utf8mb4;
SET time_zone = '+01:00';

-- ---------------------------------------------------------------- People

CREATE TABLE users (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  email         VARCHAR(190) NOT NULL,
  pass          VARCHAR(255) NOT NULL DEFAULT '',
  name          VARCHAR(120) NOT NULL DEFAULT '',
  phone         VARCHAR(40)  NOT NULL DEFAULT '',
  country       VARCHAR(80)  NOT NULL DEFAULT '',
  role          ENUM('member','partner','staff','owner') NOT NULL DEFAULT 'member',
  avatar        VARCHAR(255) NOT NULL DEFAULT '',
  -- What they have agreed to be reached on. Email is implicit and always on.
  notify        VARCHAR(60)  NOT NULL DEFAULT '',
  -- NULL until they follow the link we emailed. Only ever asked of people who sign up while the
  -- admin has email confirmation switched on (see Verify); nobody is asked while it is off.
  email_verified_at DATETIME NULL,
  wp_id         INT UNSIGNED NULL,
  created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_seen_at  DATETIME     NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_email (email),
  UNIQUE KEY uq_wp (wp_id),
  KEY ix_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sessions (
  id          CHAR(64)     NOT NULL,
  user_id     INT UNSIGNED NOT NULL,
  ip          VARBINARY(16) NULL,
  agent       VARCHAR(190) NOT NULL DEFAULT '',
  created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  expires_at  DATETIME     NOT NULL,
  PRIMARY KEY (id),
  KEY ix_user (user_id),
  KEY ix_expires (expires_at),
  CONSTRAINT fk_sess_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tokens (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id    INT UNSIGNED NULL,
  kind       ENUM('reset','verify','invite','squad','magic') NOT NULL,
  token      CHAR(64)     NOT NULL,
  ref_id     INT UNSIGNED NULL,
  data       JSON         NULL,
  used_at    DATETIME     NULL,
  expires_at DATETIME     NOT NULL,
  created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_token (token),
  KEY ix_user_kind (user_id, kind)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------- Weddings

CREATE TABLE weddings (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  owner_id    INT UNSIGNED NOT NULL,
  slug        VARCHAR(120) NOT NULL,
  partner_a   VARCHAR(120) NOT NULL DEFAULT '',
  partner_b   VARCHAR(120) NOT NULL DEFAULT '',
  hashtag     VARCHAR(80)  NOT NULL DEFAULT '',
  tagline     VARCHAR(255) NOT NULL DEFAULT '',
  story       MEDIUMTEXT   NULL,
  wed_date    DATE         NULL,
  wed_time    TIME         NULL,
  city        VARCHAR(120) NOT NULL DEFAULT '',
  state       VARCHAR(120) NOT NULL DEFAULT '',
  country     VARCHAR(80)  NOT NULL DEFAULT 'Nigeria',
  venue       VARCHAR(255) NOT NULL DEFAULT '',
  cover       VARCHAR(255) NOT NULL DEFAULT '',
  plan        ENUM('free','pro','premium','society') NOT NULL DEFAULT 'free',
  plan_until  DATE         NULL,
  template    VARCHAR(40)  NOT NULL DEFAULT 'classic',
  -- The couple's own colours, fonts and layout. One JSON column rather than forty meta rows.
  design      JSON         NULL,
  features    JSON         NULL,
  currency    CHAR(3)      NOT NULL DEFAULT 'NGN',
  visibility  ENUM('public','unlisted','private') NOT NULL DEFAULT 'public',
  pass_code   VARCHAR(40)  NOT NULL DEFAULT '',
  buzz        INT          NOT NULL DEFAULT 0,
  views       INT UNSIGNED NOT NULL DEFAULT 0,
  wp_id       INT UNSIGNED NULL,
  created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_slug (slug),
  UNIQUE KEY uq_wp (wp_id),
  KEY ix_owner (owner_id),
  KEY ix_plan (plan),
  KEY ix_date (wed_date),
  KEY ix_buzz (buzz),
  KEY ix_place (country, state, city),
  CONSTRAINT fk_wed_owner FOREIGN KEY (owner_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE programs (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  wedding_id INT UNSIGNED NOT NULL,
  title      VARCHAR(160) NOT NULL,
  kind       VARCHAR(40)  NOT NULL DEFAULT 'other',
  starts_at  DATETIME     NULL,
  venue      VARCHAR(255) NOT NULL DEFAULT '',
  address    VARCHAR(255) NOT NULL DEFAULT '',
  map_url    VARCHAR(500) NOT NULL DEFAULT '',
  dress      VARCHAR(160) NOT NULL DEFAULT '',
  note       TEXT         NULL,
  is_private TINYINT(1)   NOT NULL DEFAULT 0,
  sort       SMALLINT     NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY ix_wed (wedding_id, sort),
  CONSTRAINT fk_prog_wed FOREIGN KEY (wedding_id) REFERENCES weddings (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE guests (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  wedding_id  INT UNSIGNED NOT NULL,
  name        VARCHAR(160) NOT NULL,
  email       VARCHAR(190) NOT NULL DEFAULT '',
  phone       VARCHAR(40)  NOT NULL DEFAULT '',
  party       SMALLINT     NOT NULL DEFAULT 1,
  coming      ENUM('yes','no','maybe') NOT NULL DEFAULT 'yes',
  programs    JSON         NULL,
  note        TEXT         NULL,
  -- NULL rather than '' so a unique index can hold many guests who have no pass yet;
  -- MySQL allows repeated NULLs in a unique key but not repeated empty strings.
  pass_code   VARCHAR(40)  NULL DEFAULT NULL,
  checked_in  DATETIME     NULL,
  table_no    VARCHAR(20)  NOT NULL DEFAULT '',
  created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_pass (pass_code),
  KEY ix_wed (wedding_id, created_at),
  KEY ix_coming (wedding_id, coming),
  CONSTRAINT fk_guest_wed FOREIGN KEY (wedding_id) REFERENCES weddings (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE squad (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  wedding_id INT UNSIGNED NOT NULL,
  side       ENUM('bride','groom','both') NOT NULL DEFAULT 'both',
  name       VARCHAR(160) NOT NULL DEFAULT '',
  role       VARCHAR(120) NOT NULL DEFAULT '',
  photo      VARCHAR(255) NOT NULL DEFAULT '',
  words      TEXT         NULL,
  work       VARCHAR(160) NOT NULL DEFAULT '',
  hobby      VARCHAR(160) NOT NULL DEFAULT '',
  relationship VARCHAR(40) NOT NULL DEFAULT '',
  social     VARCHAR(255) NOT NULL DEFAULT '',
  instagram  VARCHAR(120) NOT NULL DEFAULT '',
  status     ENUM('invited','joined','hidden') NOT NULL DEFAULT 'invited',
  sort       SMALLINT     NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY ix_wed (wedding_id, sort),
  CONSTRAINT fk_squad_wed FOREIGN KEY (wedding_id) REFERENCES weddings (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE gifts (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  wedding_id INT UNSIGNED NOT NULL,
  title      VARCHAR(190) NOT NULL,
  note       TEXT         NULL,
  image      VARCHAR(255) NOT NULL DEFAULT '',
  target     BIGINT       NOT NULL DEFAULT 0,
  raised     BIGINT       NOT NULL DEFAULT 0,
  kind       ENUM('cash','item','fund') NOT NULL DEFAULT 'cash',
  link       VARCHAR(500) NOT NULL DEFAULT '',
  sort       SMALLINT     NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY ix_wed (wedding_id, sort),
  CONSTRAINT fk_gift_wed FOREIGN KEY (wedding_id) REFERENCES weddings (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE wall (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  wedding_id INT UNSIGNED NOT NULL,
  kind       ENUM('note','photo','voice') NOT NULL DEFAULT 'note',
  name       VARCHAR(160) NOT NULL DEFAULT '',
  body       TEXT         NULL,
  image      VARCHAR(255) NOT NULL DEFAULT '',
  audio      VARCHAR(255) NOT NULL DEFAULT '',
  likes      INT UNSIGNED NOT NULL DEFAULT 0,
  status     ENUM('live','held','hidden') NOT NULL DEFAULT 'live',
  created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY ix_wed (wedding_id, status, created_at),
  CONSTRAINT fk_wall_wed FOREIGN KEY (wedding_id) REFERENCES weddings (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------- Money

CREATE TABLE payments (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  ref         VARCHAR(64)  NOT NULL,
  purpose     ENUM('plan','gift','spray','order','ad','cards','custom') NOT NULL,
  wedding_id  INT UNSIGNED NULL,
  user_id     INT UNSIGNED NULL,
  gift_id     INT UNSIGNED NULL,
  order_id    INT UNSIGNED NULL,
  payer_name  VARCHAR(160) NOT NULL DEFAULT '',
  payer_email VARCHAR(190) NOT NULL DEFAULT '',
  payer_phone VARCHAR(40)  NOT NULL DEFAULT '',
  message     VARCHAR(500) NOT NULL DEFAULT '',
  amount      BIGINT       NOT NULL DEFAULT 0,
  fee         BIGINT       NOT NULL DEFAULT 0,
  currency    CHAR(3)      NOT NULL DEFAULT 'NGN',
  gateway     VARCHAR(40)  NOT NULL DEFAULT '',
  gateway_ref VARCHAR(190) NOT NULL DEFAULT '',
  status      ENUM('pending','paid','failed','refunded','released') NOT NULL DEFAULT 'pending',
  paid_at     DATETIME     NULL,
  released_at DATETIME     NULL,
  created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_ref (ref),
  KEY ix_wed (wedding_id, status),
  KEY ix_user (user_id, status),
  KEY ix_status (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE payouts (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id    INT UNSIGNED NOT NULL,
  amount     BIGINT       NOT NULL DEFAULT 0,
  currency   CHAR(3)      NOT NULL DEFAULT 'NGN',
  bank       VARCHAR(120) NOT NULL DEFAULT '',
  account    VARCHAR(60)  NOT NULL DEFAULT '',
  account_nm VARCHAR(160) NOT NULL DEFAULT '',
  status     ENUM('requested','sent','failed') NOT NULL DEFAULT 'requested',
  note       VARCHAR(255) NOT NULL DEFAULT '',
  created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  sent_at    DATETIME     NULL,
  PRIMARY KEY (id),
  KEY ix_user (user_id, status),
  CONSTRAINT fk_payout_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------- Vendors & the marketplace

CREATE TABLE vendors (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id    INT UNSIGNED NULL,
  slug       VARCHAR(140) NOT NULL,
  name       VARCHAR(190) NOT NULL,
  category   VARCHAR(80)  NOT NULL DEFAULT '',
  city       VARCHAR(120) NOT NULL DEFAULT '',
  state      VARCHAR(120) NOT NULL DEFAULT '',
  about      TEXT         NULL,
  phone      VARCHAR(40)  NOT NULL DEFAULT '',
  instagram  VARCHAR(120) NOT NULL DEFAULT '',
  website    VARCHAR(255) NOT NULL DEFAULT '',
  logo       VARCHAR(255) NOT NULL DEFAULT '',
  verified   TINYINT(1)   NOT NULL DEFAULT 0,
  verified_until DATE     NULL,
  rating     DECIMAL(3,2) NOT NULL DEFAULT 0,
  reviews    INT UNSIGNED NOT NULL DEFAULT 0,
  projects   JSON         NULL,          -- their three best pieces of work: [title, about, photo]
  status     VARCHAR(20)  NOT NULL DEFAULT 'active',   -- active, suspended (checking them) or removed
  status_at  DATETIME     NULL,
  wp_id      INT UNSIGNED NULL,
  created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_slug (slug),
  UNIQUE KEY uq_wp (wp_id),
  KEY ix_cat (category, state, city),
  KEY ix_verified (verified)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reviews (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  vendor_id  INT UNSIGNED NOT NULL,
  user_id    INT UNSIGNED NULL,
  name       VARCHAR(160) NOT NULL DEFAULT '',
  stars      TINYINT      NOT NULL DEFAULT 5,
  body       TEXT         NULL,
  reply      TEXT         NULL,
  status     ENUM('live','held','hidden') NOT NULL DEFAULT 'live',
  created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY ix_vendor (vendor_id, status, created_at),
  CONSTRAINT fk_rev_vendor FOREIGN KEY (vendor_id) REFERENCES vendors (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE listings (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  partner_id INT UNSIGNED NOT NULL,
  slug       VARCHAR(160) NOT NULL,
  title      VARCHAR(190) NOT NULL,
  kind       VARCHAR(40)  NOT NULL DEFAULT 'service',   -- a category slug: only HQ opens new ones
  about      MEDIUMTEXT   NULL,
  city       VARCHAR(120) NOT NULL DEFAULT '',
  state      VARCHAR(120) NOT NULL DEFAULT '',
  price      BIGINT       NOT NULL DEFAULT 0,
  price_unit VARCHAR(40)  NOT NULL DEFAULT '',
  on_ask     TINYINT(1)   NOT NULL DEFAULT 0,
  photos     JSON         NULL,
  status     ENUM('live','draft','paused','held') NOT NULL DEFAULT 'draft',
  wp_id      INT UNSIGNED NULL,
  created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_slug (slug),
  UNIQUE KEY uq_wp (wp_id),
  KEY ix_partner (partner_id, status),
  KEY ix_kind (kind, state, city, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE orders (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  code        VARCHAR(32)  NOT NULL,
  listing_id  INT UNSIGNED NULL,
  partner_id  INT UNSIGNED NOT NULL,
  user_id     INT UNSIGNED NULL,
  name        VARCHAR(160) NOT NULL DEFAULT '',
  email       VARCHAR(190) NOT NULL DEFAULT '',
  phone       VARCHAR(40)  NOT NULL DEFAULT '',
  detail      TEXT         NULL,
  amount      BIGINT       NOT NULL DEFAULT 0,
  currency    CHAR(3)      NOT NULL DEFAULT 'NGN',
  -- Money is held until the buyer confirms, then released to the seller.
  status      ENUM('quote','awaiting','paid','released','disputed','refunded','cancelled') NOT NULL DEFAULT 'quote',
  starts_on   DATE         NULL,
  ends_on     DATE         NULL,
  created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  released_at DATETIME     NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_code (code),
  KEY ix_partner (partner_id, status),
  KEY ix_user (user_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE messages (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  partner_id INT UNSIGNED NOT NULL,
  user_id    INT UNSIGNED NOT NULL,
  listing_id INT UNSIGNED NULL,
  sender     ENUM('user','partner','staff') NOT NULL,
  body       TEXT         NOT NULL,
  invoice_id INT UNSIGNED NULL,
  read_at    DATETIME     NULL,
  created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY ix_thread (partner_id, user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- What can be sold. HQ adds and opens categories; sellers can only list in an open one.
CREATE TABLE categories (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug       VARCHAR(40)  NOT NULL,
  name       VARCHAR(80)  NOT NULL,
  about      VARCHAR(255) NOT NULL DEFAULT '',
  open       TINYINT(1)   NOT NULL DEFAULT 1,
  sort       SMALLINT     NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- An invoice or a payment link a seller sends in a chat. We never hold the money: the link is the
-- seller's own, and the customer pays them directly.
CREATE TABLE invoices (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  code       VARCHAR(16)  NOT NULL,
  kind       ENUM('invoice','link') NOT NULL DEFAULT 'invoice',
  partner_id INT UNSIGNED NOT NULL,
  user_id    INT UNSIGNED NOT NULL,
  listing_id INT UNSIGNED NULL,
  title      VARCHAR(190) NOT NULL DEFAULT '',
  lines_json TEXT         NULL,
  amount     BIGINT       NOT NULL DEFAULT 0,
  currency   CHAR(3)      NOT NULL DEFAULT 'NGN',
  pay_link   VARCHAR(500) NOT NULL DEFAULT '',
  pay_info   TEXT         NULL,
  due_on     DATE         NULL,
  note       TEXT         NULL,
  status     ENUM('open','paid','cancelled') NOT NULL DEFAULT 'open',
  created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  paid_at    DATETIME     NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_code (code),
  KEY ix_thread (partner_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- A customer's report that a seller may not be genuine. One per customer per seller.
CREATE TABLE seller_reports (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  vendor_id  INT UNSIGNED NOT NULL,
  user_id    INT UNSIGNED NOT NULL,
  listing_id INT UNSIGNED NULL,
  reason     VARCHAR(40)  NOT NULL DEFAULT '',
  body       TEXT         NULL,
  status     ENUM('open','cleared') NOT NULL DEFAULT 'open',
  created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_one (vendor_id, user_id),
  KEY ix_vendor (vendor_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------- The anonymous community

CREATE TABLE talks (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  topic      VARCHAR(40)  NOT NULL DEFAULT 'confess',
  -- The name the writer typed. No account is attached and none is ever shown.
  pen_name   VARCHAR(40)  NOT NULL DEFAULT '',
  body       MEDIUMTEXT   NOT NULL,
  bg         VARCHAR(20)  NOT NULL DEFAULT 'paper',
  likes      INT UNSIGNED NOT NULL DEFAULT 0,
  replies    INT UNSIGNED NOT NULL DEFAULT 0,
  reports    SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  status     ENUM('live','held','hidden') NOT NULL DEFAULT 'live',
  -- A private key so a visitor can delete their own words, never shown and never reversible.
  writer_key CHAR(64)     NOT NULL DEFAULT '',
  is_seed    TINYINT(1)   NOT NULL DEFAULT 0,
  wp_id      INT UNSIGNED NULL,
  created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_wp (wp_id),
  KEY ix_feed (status, created_at),
  KEY ix_top (status, likes),
  KEY ix_topic (topic, status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE talk_replies (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  talk_id    INT UNSIGNED NOT NULL,
  parent_id  INT UNSIGNED NULL,   -- the reply this answers; NULL answers the post itself
  pen_name   VARCHAR(40)  NOT NULL DEFAULT '',
  body       TEXT         NOT NULL,
  likes      INT UNSIGNED NOT NULL DEFAULT 0,
  reports    SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  status     ENUM('live','held','hidden') NOT NULL DEFAULT 'live',
  writer_key CHAR(64)     NOT NULL DEFAULT '',
  created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY ix_talk (talk_id, status, created_at),
  CONSTRAINT fk_reply_talk FOREIGN KEY (talk_id) REFERENCES talks (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- One row per person per thing, so a like cannot be counted twice.
CREATE TABLE likes (
  subject    ENUM('talk','reply','wall','photo') NOT NULL,
  subject_id INT UNSIGNED NOT NULL,
  voter      CHAR(64)     NOT NULL,
  created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (subject, subject_id, voter)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------- Content & the platform

CREATE TABLE posts (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug       VARCHAR(190) NOT NULL,
  title      VARCHAR(255) NOT NULL,
  excerpt    VARCHAR(500) NOT NULL DEFAULT '',
  body       LONGTEXT     NULL,
  source     MEDIUMTEXT   NULL,
  cover      VARCHAR(255) NOT NULL DEFAULT '',
  category   VARCHAR(80)  NOT NULL DEFAULT '',
  -- The section: a guide (written for search), news, or a feature or interview.
  kind       ENUM('guide','news','feature') NOT NULL DEFAULT 'guide',
  author     VARCHAR(120) NOT NULL DEFAULT '',
  meta_title VARCHAR(190) NOT NULL DEFAULT '',
  meta_desc  VARCHAR(300) NOT NULL DEFAULT '',
  featured   TINYINT(1)   NOT NULL DEFAULT 0,
  status     ENUM('live','draft') NOT NULL DEFAULT 'draft',
  wp_id      INT UNSIGNED NULL,
  published_at DATETIME   NULL,
  updated_at DATETIME     NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_slug (slug),
  UNIQUE KEY uq_wp (wp_id),
  KEY ix_live (status, published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE settings (
  k          VARCHAR(80)  NOT NULL,
  v          LONGTEXT     NULL,
  PRIMARY KEY (k)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Simple rate limiting, so a shared host is not the thing that stops abuse.
CREATE TABLE throttle (
  bucket     VARCHAR(120) NOT NULL,
  hits       SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  expires_at DATETIME     NOT NULL,
  PRIMARY KEY (bucket),
  KEY ix_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE media (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  owner_id   INT UNSIGNED NULL,
  wedding_id INT UNSIGNED NULL,
  path       VARCHAR(255) NOT NULL,
  kind       VARCHAR(40)  NOT NULL DEFAULT 'image',
  width      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  height     SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  bytes      INT UNSIGNED NOT NULL DEFAULT 0,
  wp_id      INT UNSIGNED NULL,
  created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_wp (wp_id),
  KEY ix_wed (wedding_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- A record of what the importer brought across, so it can be re-run safely and audited.
CREATE TABLE import_log (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  kind       VARCHAR(40)  NOT NULL,
  wp_id      INT UNSIGNED NOT NULL,
  new_id     INT UNSIGNED NULL,
  note       VARCHAR(255) NOT NULL DEFAULT '',
  created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_kind_wp (kind, wp_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- "Who knows them best?": one row per guest who played a partner's quiz.
CREATE TABLE quiz_scores (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  wedding_id INT UNSIGNED NOT NULL,
  side       CHAR(1)      NOT NULL,
  name       VARCHAR(80)  NOT NULL DEFAULT '',
  score      TINYINT UNSIGNED NOT NULL DEFAULT 0,
  total      TINYINT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY board (wedding_id, side, score),
  CONSTRAINT quiz_scores_wedding FOREIGN KEY (wedding_id) REFERENCES weddings (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
