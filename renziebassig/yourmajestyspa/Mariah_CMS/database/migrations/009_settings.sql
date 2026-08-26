-- =============================================================
-- 009 — Site settings
--
-- Key/value, with every setting's type, default, label, help text and
-- validation declared in app/Services/SettingsSchema.php. That file is the
-- source of truth, exactly as config/permissions.php is for permissions.
--
-- A missing row means "use the registry default", so this table only ever
-- holds values an operator explicitly set. Nothing is seeded.
--
-- `setting_key` / `setting_value` rather than `key` / `value`: KEY is a MySQL
-- reserved word and this repository hand-writes its SQL.
-- =============================================================

CREATE TABLE IF NOT EXISTS settings (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  setting_key   VARCHAR(100) NOT NULL,
  setting_value TEXT         NULL,

  updated_by    INT UNSIGNED NULL,
  created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  UNIQUE KEY uq_settings_key (setting_key),
  KEY idx_settings_user (updated_by),
  CONSTRAINT fk_settings_user FOREIGN KEY (updated_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
