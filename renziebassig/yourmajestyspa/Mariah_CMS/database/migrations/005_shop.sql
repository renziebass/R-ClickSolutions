-- =============================================================
-- 005 — Retail shop: brands, product categories, products
-- Drives the public page's brand cards, type chips and #prodGrid.
-- =============================================================

CREATE TABLE IF NOT EXISTS product_brands (
  id            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  name          VARCHAR(120)  NOT NULL,
  slug          VARCHAR(190)  NOT NULL,
  tagline       VARCHAR(190)  NULL,   -- "Botanical, results-driven"
  media_id      INT UNSIGNED  NULL,
  status        ENUM('active','inactive') NOT NULL DEFAULT 'active',
  display_order INT           NOT NULL DEFAULT 0,
  created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_by    INT UNSIGNED  NULL,
  updated_by    INT UNSIGNED  NULL,
  deleted_at    DATETIME      NULL,
  deleted_by    INT UNSIGNED  NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_brands_slug (slug),
  KEY idx_brands_public (status, deleted_at, display_order),
  CONSTRAINT fk_brands_media FOREIGN KEY (media_id) REFERENCES media (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_categories (
  id            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  name          VARCHAR(120)  NOT NULL,
  slug          VARCHAR(190)  NOT NULL,
  description   VARCHAR(500)  NULL,
  status        ENUM('active','inactive') NOT NULL DEFAULT 'active',
  display_order INT           NOT NULL DEFAULT 0,
  created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_by    INT UNSIGNED  NULL,
  updated_by    INT UNSIGNED  NULL,
  deleted_at    DATETIME      NULL,
  deleted_by    INT UNSIGNED  NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_product_categories_slug (slug),
  KEY idx_prod_cat_public (status, deleted_at, display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS products (
  id               INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  brand_id         INT UNSIGNED  NULL,
  category_id      INT UNSIGNED  NULL,
  name             VARCHAR(190)  NOT NULL,
  slug             VARCHAR(190)  NOT NULL,
  description      TEXT          NULL,
  price            DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  compare_at_price DECIMAL(10,2) NULL,
  media_id         INT UNSIGNED  NULL,
  -- Sprite symbol used when no photo is set: i-pad, i-bottle, i-pump, i-jar.
  icon_key         VARCHAR(40)   NULL,
  badge_label      VARCHAR(60)   NULL,   -- "Best seller", "Low stock"
  status           ENUM('active','inactive') NOT NULL DEFAULT 'active',
  featured         TINYINT(1)    NOT NULL DEFAULT 0,
  display_order    INT           NOT NULL DEFAULT 0,
  created_at       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_by       INT UNSIGNED  NULL,
  updated_by       INT UNSIGNED  NULL,
  deleted_at       DATETIME      NULL,
  deleted_by       INT UNSIGNED  NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_products_slug (slug),
  KEY idx_products_public (status, deleted_at, display_order),
  KEY idx_products_brand (brand_id),
  KEY idx_products_category (category_id, status, display_order),
  KEY idx_products_media (media_id),
  -- RESTRICT: brands/categories with products attached cannot be hard-deleted.
  CONSTRAINT fk_products_brand    FOREIGN KEY (brand_id)    REFERENCES product_brands (id)     ON DELETE RESTRICT,
  CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES product_categories (id) ON DELETE RESTRICT,
  CONSTRAINT fk_products_media    FOREIGN KEY (media_id)    REFERENCES media (id)              ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
