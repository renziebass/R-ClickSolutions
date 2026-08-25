-- =============================================================
-- 004 — Promotions and Specials
--
-- These are separate entities on purpose:
--   promotion — a DISCOUNT RULE applied to one or more services
--               (percentage / fixed amount / special promotional price)
--   special   — a SELLABLE BUNDLE at its own price; the public page renders
--               it as "$215" struck through "$299" with a badge.
--
-- `status` only ever holds draft / published / archived. The public-facing
-- state (scheduled / active / expired) is DERIVED from status + dates by
-- App\Services\ScheduleResolver, never typed in by a person.
-- =============================================================

CREATE TABLE IF NOT EXISTS promotions (
  id             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  title          VARCHAR(190)  NOT NULL,
  slug           VARCHAR(190)  NOT NULL,
  description    TEXT          NULL,
  media_id       INT UNSIGNED  NULL,

  discount_type  ENUM('percentage','fixed','special_price') NOT NULL DEFAULT 'percentage',
  discount_value DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  original_price DECIMAL(10,2) NULL,
  promo_price    DECIMAL(10,2) NULL,

  badge_label    VARCHAR(60)   NULL,
  booking_url    VARCHAR(500)  NULL,
  start_date     DATE          NULL,   -- NULL = starts immediately
  end_date       DATE          NULL,   -- NULL = never expires

  status         ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
  featured       TINYINT(1)    NOT NULL DEFAULT 0,
  display_order  INT           NOT NULL DEFAULT 0,

  created_at     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_by     INT UNSIGNED  NULL,
  updated_by     INT UNSIGNED  NULL,
  deleted_at     DATETIME      NULL,
  deleted_by     INT UNSIGNED  NULL,

  PRIMARY KEY (id),
  UNIQUE KEY uq_promotions_slug (slug),
  KEY idx_promotions_public (status, deleted_at, display_order),
  KEY idx_promotions_window (status, start_date, end_date),
  KEY idx_promotions_media (media_id),
  CONSTRAINT fk_promotions_media FOREIGN KEY (media_id) REFERENCES media (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Which services a promotion applies to.
CREATE TABLE IF NOT EXISTS promotion_services (
  promotion_id INT UNSIGNED NOT NULL,
  service_id   INT UNSIGNED NOT NULL,
  PRIMARY KEY (promotion_id, service_id),
  KEY idx_ps_service (service_id),
  CONSTRAINT fk_ps_promotion FOREIGN KEY (promotion_id) REFERENCES promotions (id) ON DELETE CASCADE,
  CONSTRAINT fk_ps_service   FOREIGN KEY (service_id)   REFERENCES services (id)   ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS specials (
  id               INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  title            VARCHAR(190)  NOT NULL,
  slug             VARCHAR(190)  NOT NULL,
  description      TEXT          NULL,
  media_id         INT UNSIGNED  NULL,

  badge_label      VARCHAR(60)   NULL,   -- "Seasonal", "For two", "Members"
  price            DECIMAL(10,2) NULL,
  price_display    VARCHAR(60)   NULL,   -- e.g. "From $109 / mo"
  compare_at_price DECIMAL(10,2) NULL,   -- the struck-through original
  booking_url      VARCHAR(500)  NULL,

  start_date       DATE          NULL,
  end_date         DATE          NULL,

  status           ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
  featured         TINYINT(1)    NOT NULL DEFAULT 0,
  display_order    INT           NOT NULL DEFAULT 0,

  created_at       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_by       INT UNSIGNED  NULL,
  updated_by       INT UNSIGNED  NULL,
  deleted_at       DATETIME      NULL,
  deleted_by       INT UNSIGNED  NULL,

  PRIMARY KEY (id),
  UNIQUE KEY uq_specials_slug (slug),
  KEY idx_specials_public (status, deleted_at, display_order),
  KEY idx_specials_window (status, start_date, end_date),
  KEY idx_specials_media (media_id),
  CONSTRAINT fk_specials_media FOREIGN KEY (media_id) REFERENCES media (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
