-- =============================================================
-- 007 — Audit log
-- Append-only record of administrative activity. Rows survive the deletion of
-- the acting user (user_id becomes NULL) so history is never rewritten.
-- =============================================================

CREATE TABLE IF NOT EXISTS audit_logs (
  id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id     INT UNSIGNED    NULL,
  user_label  VARCHAR(190)    NULL,   -- denormalised name at time of action
  action      VARCHAR(60)     NOT NULL,   -- created, updated, deleted, login…
  entity_type VARCHAR(60)     NOT NULL,   -- service, promotion, user…
  entity_id   INT UNSIGNED    NULL,
  description VARCHAR(500)    NOT NULL,
  metadata    JSON            NULL,       -- changed fields, before/after
  ip_address  VARCHAR(45)     NULL,
  user_agent  VARCHAR(255)    NULL,
  created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_audit_entity (entity_type, entity_id),
  KEY idx_audit_user (user_id, created_at),
  KEY idx_audit_action (action, created_at),
  KEY idx_audit_created (created_at),
  CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
