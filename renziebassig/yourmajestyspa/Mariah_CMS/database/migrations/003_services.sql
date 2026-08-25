-- =============================================================
-- 003 — Service categories, services, service images
-- =============================================================

CREATE TABLE IF NOT EXISTS service_categories (
  id            INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  name          VARCHAR(120)   NOT NULL,
  slug          VARCHAR(190)   NOT NULL,
  description   TEXT           NULL,
  -- Maps to an <symbol> id in the public page's SVG sprite, e.g. "i-hands".
  icon_key      VARCHAR(40)    NULL,
  media_id      INT UNSIGNED   NULL,
  status        ENUM('active','inactive') NOT NULL DEFAULT 'active',
  display_order INT            NOT NULL DEFAULT 0,
  created_at    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_by    INT UNSIGNED   NULL,
  updated_by    INT UNSIGNED   NULL,
  deleted_at    DATETIME       NULL,
  deleted_by    INT UNSIGNED   NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_service_categories_slug (slug),
  -- Exactly the shape of the public listing query.
  KEY idx_svc_cat_public (status, deleted_at, display_order),
  KEY idx_svc_cat_media (media_id),
  CONSTRAINT fk_svc_cat_media FOREIGN KEY (media_id) REFERENCES media (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS services (
  id                INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  category_id       INT UNSIGNED   NOT NULL,
  name              VARCHAR(190)   NOT NULL,
  slug              VARCHAR(190)   NOT NULL,
  short_description VARCHAR(500)   NULL,
  description       TEXT           NULL,

  -- Numeric price drives sorting/filtering/reporting.
  price             DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
  -- Optional display override: the live site shows "from $150", "$199 – $225".
  price_display     VARCHAR(60)    NULL,
  promo_price       DECIMAL(10,2)  NULL,

  duration_minutes  INT UNSIGNED   NULL,
  -- Optional display override: "1 hr & 40 mins", "60 – 90 min".
  duration_display  VARCHAR(60)    NULL,

  icon_key          VARCHAR(40)    NULL,
  booking_url       VARCHAR(500)   NULL,   -- per-service Booker.com deep link
  media_id          INT UNSIGNED   NULL,   -- primary image shortcut

  status            ENUM('active','inactive') NOT NULL DEFAULT 'active',
  featured          TINYINT(1)     NOT NULL DEFAULT 0,
  -- 1–3 drives the public "Most Loved" ranking; NULL means unranked.
  most_loved_rank   TINYINT UNSIGNED NULL,
  display_order     INT            NOT NULL DEFAULT 0,

  created_at        DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_by        INT UNSIGNED   NULL,
  updated_by        INT UNSIGNED   NULL,
  deleted_at        DATETIME       NULL,
  deleted_by        INT UNSIGNED   NULL,

  PRIMARY KEY (id),
  UNIQUE KEY uq_services_slug (slug),
  KEY idx_services_public (status, deleted_at, display_order),
  KEY idx_services_category (category_id, status, display_order),
  KEY idx_services_featured (featured, status, deleted_at),
  KEY idx_services_most_loved (most_loved_rank),
  KEY idx_services_media (media_id),
  KEY idx_services_updated (updated_at),
  -- RESTRICT: a category with services attached cannot be hard-deleted.
  CONSTRAINT fk_services_category FOREIGN KEY (category_id) REFERENCES service_categories (id) ON DELETE RESTRICT,
  CONSTRAINT fk_services_media    FOREIGN KEY (media_id)    REFERENCES media (id)              ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Multiple images per service (gallery). The primary one is mirrored onto
-- services.media_id so public listing queries need no join.
CREATE TABLE IF NOT EXISTS service_images (
  id            INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  service_id    INT UNSIGNED   NOT NULL,
  media_id      INT UNSIGNED   NOT NULL,
  alt_text      VARCHAR(255)   NULL,
  display_order INT            NOT NULL DEFAULT 0,
  is_primary    TINYINT(1)     NOT NULL DEFAULT 0,
  uploaded_by   INT UNSIGNED   NULL,
  created_at    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_service_media (service_id, media_id),
  KEY idx_svc_img_order (service_id, display_order),
  KEY idx_svc_img_media (media_id),
  CONSTRAINT fk_svc_img_service FOREIGN KEY (service_id) REFERENCES services (id) ON DELETE CASCADE,
  -- RESTRICT: media still attached to a service cannot be hard-deleted.
  CONSTRAINT fk_svc_img_media   FOREIGN KEY (media_id)   REFERENCES media (id)    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
