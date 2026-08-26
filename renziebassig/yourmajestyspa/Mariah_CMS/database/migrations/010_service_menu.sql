-- =============================================================
-- 010 — Service sub-categories, price/duration variants, add-ons
--
-- The real treatment menu needs three things 003 could not express:
--
--   * two levels of category. "Signature Massage" and "Specialty Massage"
--     are both Massage; the parent level was implicit in the spreadsheet.
--   * several duration/price tiers per service. Hot Stone Massage is
--     50m/$150, 80m/$180 and 1h50m/$210. That is why services.price_display
--     and services.duration_display exist at all — they were holding
--     "from $150" and "60 – 90 min" as free text because there was nowhere
--     else to put a range. Those columns stay as manual overrides; they are
--     no longer the only way to say it.
--   * an add-on menu per category, priced per category. "Aromatherapy" is
--     +$25 on the massage menu and +$20 on the facial menu, so add-ons are
--     rows owned by a category, not a shared catalogue.
--
-- Deliberately absent: gratuity. It is exactly 15% of price throughout the
-- source menu, so it is computed, never stored. (The one row that disagreed
-- was stale data, not a different rule.)
--
-- ORDERING: the CREATEs run before the ALTERs on purpose. migrate() records
-- a file only after the whole thing succeeds and MySQL commits DDL
-- implicitly, so a mid-file failure leaves the file unrecorded and a
-- corrected re-run replays it from the top. CREATE TABLE IF NOT EXISTS is
-- a no-op on replay; ALTER TABLE ADD COLUMN is not — it fails with
-- "duplicate column". Keeping the ALTERs last means the statements most
-- likely to fail are also the ones nothing runs after.
-- =============================================================

-- --- Price / duration tiers ----------------------------------------
-- Subordinate child of services, shaped like service_images: no soft delete,
-- no authorship, CASCADE from the parent, ordered by display_order. Edited
-- only inside the service form, never through a repository of its own.
--
-- booking_url is per tier on purpose. The Booker links are
-- .../detail-summary/{id} deep links, so 50 minutes and 80 minutes of the
-- same treatment are two different products to book.
--
-- The cheapest tier is mirrored back onto services.price and
-- services.duration_minutes by syncVariants(), exactly as service_images
-- mirrors its primary onto services.media_id — so every existing sort,
-- filter and public query keeps working with no join.
CREATE TABLE IF NOT EXISTS service_variants (
  id               INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  service_id       INT UNSIGNED   NOT NULL,
  label            VARCHAR(60)    NOT NULL,   -- "50 min", "1 hr 50 min"
  duration_minutes INT UNSIGNED   NULL,
  price            DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
  booking_url      VARCHAR(500)   NULL,
  display_order    INT            NOT NULL DEFAULT 0,
  created_at       DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_svc_var_order (service_id, display_order),
  CONSTRAINT fk_svc_var_service FOREIGN KEY (service_id)
      REFERENCES services (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --- Add-ons -------------------------------------------------------
-- A first-class record with its own admin list, so it carries the full
-- BaseRepository column contract (status, display_order, audit, soft delete).
-- No slug: an add-on is never addressed by URL.
--
-- CASCADE from the category: an add-on menu has no meaning without the
-- category it enhances.
CREATE TABLE IF NOT EXISTS service_addons (
  id               INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  category_id      INT UNSIGNED   NOT NULL,
  name             VARCHAR(150)   NOT NULL,
  description      VARCHAR(500)   NULL,
  price            DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
  duration_minutes INT UNSIGNED   NULL,
  status           ENUM('active','inactive') NOT NULL DEFAULT 'active',
  display_order    INT            NOT NULL DEFAULT 0,
  created_at       DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at       DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_by       INT UNSIGNED   NULL,
  updated_by       INT UNSIGNED   NULL,
  deleted_at       DATETIME       NULL,
  deleted_by       INT UNSIGNED   NULL,
  PRIMARY KEY (id),
  KEY idx_svc_addon_public (category_id, status, deleted_at, display_order),
  CONSTRAINT fk_svc_addon_category FOREIGN KEY (category_id)
      REFERENCES service_categories (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --- Guest-facing detail -------------------------------------------
-- Four columns the source menu already had written and the CMS had nowhere
-- to keep. contraindications is the one that matters most: "heat sensitivity,
-- diabetes neuropathy" is information a guest should read before booking a
-- hot stone treatment, not after.
ALTER TABLE services
  ADD COLUMN benefits                  TEXT         NULL AFTER description,
  ADD COLUMN inclusions                TEXT         NULL AFTER benefits,
  ADD COLUMN contraindications         TEXT         NULL AFTER inclusions,
  ADD COLUMN complimentary_enhancement VARCHAR(500) NULL AFTER contraindications;

-- --- Sub-categories ------------------------------------------------
-- Self-referencing, capped at two levels in CategoryController. RESTRICT so
-- a parent holding children cannot be hard-deleted.
ALTER TABLE service_categories
  ADD COLUMN parent_id INT UNSIGNED NULL AFTER id,
  ADD KEY idx_svc_cat_parent (parent_id, display_order),
  ADD CONSTRAINT fk_svc_cat_parent FOREIGN KEY (parent_id)
      REFERENCES service_categories (id) ON DELETE RESTRICT;
