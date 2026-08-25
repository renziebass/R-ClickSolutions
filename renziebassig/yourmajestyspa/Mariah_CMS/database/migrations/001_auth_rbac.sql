-- =============================================================
-- 001 — Authentication, roles, permissions, login throttling
-- =============================================================

CREATE TABLE IF NOT EXISTS roles (
  id            INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  name          VARCHAR(80)    NOT NULL,
  slug          VARCHAR(80)    NOT NULL,
  description   VARCHAR(255)   NULL,
  -- System roles cannot be renamed or deleted from the UI.
  is_system     TINYINT(1)     NOT NULL DEFAULT 0,
  created_at    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_roles_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS permissions (
  id            INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  slug          VARCHAR(80)    NOT NULL,   -- e.g. services.create
  name          VARCHAR(120)   NOT NULL,
  group_name    VARCHAR(60)    NOT NULL,   -- UI grouping: Services, Users, …
  description   VARCHAR(255)   NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_permissions_slug (slug),
  KEY idx_permissions_group (group_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS role_permissions (
  role_id       INT UNSIGNED   NOT NULL,
  permission_id INT UNSIGNED   NOT NULL,
  PRIMARY KEY (role_id, permission_id),
  KEY idx_rp_permission (permission_id),
  CONSTRAINT fk_rp_role       FOREIGN KEY (role_id)       REFERENCES roles (id)       ON DELETE CASCADE,
  CONSTRAINT fk_rp_permission FOREIGN KEY (permission_id) REFERENCES permissions (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
  id            INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  first_name    VARCHAR(80)    NOT NULL,
  last_name     VARCHAR(80)    NOT NULL,
  email         VARCHAR(190)   NOT NULL,
  -- bcrypt hash from password_hash(). Never selected into an API response.
  password_hash VARCHAR(255)   NOT NULL,
  role_id       INT UNSIGNED   NOT NULL,
  status        ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active',
  last_login_at DATETIME       NULL,
  last_login_ip VARCHAR(45)    NULL,
  created_at    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_by    INT UNSIGNED   NULL,
  updated_by    INT UNSIGNED   NULL,
  deleted_at    DATETIME       NULL,
  deleted_by    INT UNSIGNED   NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email),
  KEY idx_users_role (role_id),
  KEY idx_users_status (status, deleted_at),
  -- RESTRICT: deleting a role that still has users must fail loudly.
  CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS login_attempts (
  id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  email        VARCHAR(190)    NOT NULL,
  ip_address   VARCHAR(45)     NOT NULL,
  successful   TINYINT(1)      NOT NULL DEFAULT 0,
  attempted_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_attempts_email (email, attempted_at),
  KEY idx_attempts_ip (ip_address, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
