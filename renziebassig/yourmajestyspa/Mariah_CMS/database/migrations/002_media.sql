-- =============================================================
-- 002 — Central media library
-- Binary data lives on disk under storage/uploads/YYYY/MM/;
-- only metadata is stored here.
-- =============================================================

CREATE TABLE IF NOT EXISTS media (
  id            INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  file_name     VARCHAR(255)   NOT NULL,   -- randomised name on disk
  original_name VARCHAR(255)   NOT NULL,   -- name as uploaded, for display
  file_path     VARCHAR(500)   NOT NULL,   -- relative to STORAGE_PATH
  file_url      VARCHAR(700)   NOT NULL,   -- public URL
  mime_type     VARCHAR(100)   NOT NULL,
  file_size     INT UNSIGNED   NOT NULL,
  width         INT UNSIGNED   NULL,
  height        INT UNSIGNED   NULL,
  alt_text      VARCHAR(255)   NULL,
  title         VARCHAR(190)   NULL,
  uploaded_by   INT UNSIGNED   NULL,
  created_at    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at    DATETIME       NULL,
  deleted_by    INT UNSIGNED   NULL,
  PRIMARY KEY (id),
  KEY idx_media_deleted (deleted_at, created_at),
  KEY idx_media_uploader (uploaded_by),
  CONSTRAINT fk_media_uploader FOREIGN KEY (uploaded_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
