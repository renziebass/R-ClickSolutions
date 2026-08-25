-- =============================================================
-- 006 — Gift cards & memberships
-- One table, discriminated by `type`: both are prepaid offerings with a
-- purchase link, they differ only in billing cadence.
-- =============================================================

CREATE TABLE IF NOT EXISTS gift_cards (
  id             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  type           ENUM('gift_card','membership') NOT NULL DEFAULT 'gift_card',
  title          VARCHAR(190)  NOT NULL,
  slug           VARCHAR(190)  NOT NULL,
  description    TEXT          NULL,
  media_id       INT UNSIGNED  NULL,

  price          DECIMAL(10,2) NULL,
  price_display  VARCHAR(60)   NULL,   -- "From $109 / mo"
  price_interval ENUM('one_time','monthly','yearly') NOT NULL DEFAULT 'one_time',
  purchase_url   VARCHAR(500)  NULL,
  badge_label    VARCHAR(60)   NULL,

  status         ENUM('active','inactive') NOT NULL DEFAULT 'active',
  featured       TINYINT(1)    NOT NULL DEFAULT 0,
  display_order  INT           NOT NULL DEFAULT 0,

  created_at     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_by     INT UNSIGNED  NULL,
  updated_by     INT UNSIGNED  NULL,
  deleted_at     DATETIME      NULL,
  deleted_by     INT UNSIGNED  NULL,

  PRIMARY KEY (id),
  UNIQUE KEY uq_gift_cards_slug (slug),
  KEY idx_gift_cards_public (status, deleted_at, display_order),
  KEY idx_gift_cards_type (type, status, deleted_at),
  KEY idx_gift_cards_media (media_id),
  CONSTRAINT fk_gift_cards_media FOREIGN KEY (media_id) REFERENCES media (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
