-- =============================================================
-- 008 — Blog (the website's "Journal" section)
--
-- A post is dated content like a promotion, but its window has only an
-- opening edge: `published_at`. The public state is DERIVED from status +
-- published_at by App\Services\ScheduleResolver::resolvePublished(), never
-- typed in by a person — so "scheduled for Friday" is one date, not a chore
-- someone has to remember on Friday morning.
--
-- `content` holds RICH TEXT — a small allowlisted subset of HTML, written in
-- the admin editor and reduced to that subset by App\Services\HtmlSanitizer on
-- every write. The public page renders it WITHOUT escaping, which is safe only
-- because nothing outside the allowlist can reach this column.
--
-- Posts written before the editor existed hold plain text with a blank line
-- between paragraphs; the page detects the absence of markup and applies the
-- old paragraph rule, so nothing had to be migrated.
-- =============================================================

CREATE TABLE IF NOT EXISTS blog_categories (
  id            INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  name          VARCHAR(120)   NOT NULL,
  slug          VARCHAR(190)   NOT NULL,
  description   TEXT           NULL,
  status        ENUM('active','inactive') NOT NULL DEFAULT 'active',
  display_order INT            NOT NULL DEFAULT 0,

  created_at    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_by    INT UNSIGNED   NULL,
  updated_by    INT UNSIGNED   NULL,
  deleted_at    DATETIME       NULL,
  deleted_by    INT UNSIGNED   NULL,

  PRIMARY KEY (id),
  UNIQUE KEY uq_blog_categories_slug (slug),
  KEY idx_blog_cat_public (status, deleted_at, display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS blog_posts (
  id               INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  category_id      INT UNSIGNED  NULL,   -- NULL = uncategorised, still publishable
  title            VARCHAR(190)  NOT NULL,
  slug             VARCHAR(190)  NOT NULL,
  -- Card teaser. Left blank, the public endpoint derives one from `content`.
  excerpt          VARCHAR(500)  NULL,
  content          MEDIUMTEXT    NULL,
  media_id         INT UNSIGNED  NULL,   -- cover image

  author_name      VARCHAR(120)  NULL,   -- byline as printed, not a user id
  -- Blank on save, and the controller computes it from the word count.
  read_minutes     SMALLINT UNSIGNED NULL,
  tags             VARCHAR(255)  NULL,   -- comma separated, shown as pills

  meta_title       VARCHAR(190)  NULL,
  meta_description VARCHAR(300)  NULL,

  status           ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
  -- NULL on a published post means "live as soon as it was published".
  published_at     DATETIME      NULL,
  featured         TINYINT(1)    NOT NULL DEFAULT 0,
  display_order    INT           NOT NULL DEFAULT 0,

  created_at       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_by       INT UNSIGNED  NULL,
  updated_by       INT UNSIGNED  NULL,
  deleted_at       DATETIME      NULL,
  deleted_by       INT UNSIGNED  NULL,

  PRIMARY KEY (id),
  UNIQUE KEY uq_blog_posts_slug (slug),
  -- Exactly the shape of the public listing query.
  KEY idx_blog_posts_public (status, deleted_at, published_at),
  KEY idx_blog_posts_category (category_id),
  KEY idx_blog_posts_media (media_id),
  CONSTRAINT fk_blog_posts_category FOREIGN KEY (category_id)
    REFERENCES blog_categories (id) ON DELETE SET NULL,
  CONSTRAINT fk_blog_posts_media FOREIGN KEY (media_id)
    REFERENCES media (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
