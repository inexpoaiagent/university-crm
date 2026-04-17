-- User-level permission overrides + safer role defaults
-- Run once on existing installations

CREATE TABLE IF NOT EXISTS user_permissions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  permission_key VARCHAR(120) NOT NULL,
  is_allowed TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_user_permission (user_id, permission_key),
  CONSTRAINT fk_user_permissions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO permissions (`key`, name, group_key) VALUES
('finance.update', 'Manage Finance', 'finance'),
('tasks.view', 'View Tasks', 'tasks'),
('tasks.create', 'Create Tasks', 'tasks'),
('tasks.update', 'Update Tasks', 'tasks'),
('tasks.delete', 'Delete Tasks', 'tasks'),
('messages.view', 'View Messages', 'messages'),
('messages.create', 'Create Messages', 'messages');

DELETE rp
FROM role_permissions rp
INNER JOIN roles r ON r.id = rp.role_id
INNER JOIN permissions p ON p.id = rp.permission_id
WHERE r.slug IN ('agent', 'sub_agent')
  AND p.`key` IN ('finance.view', 'finance.update');

INSERT IGNORE INTO role_permissions (role_id, permission_id, created_at)
SELECT r.id, p.id, NOW()
FROM roles r
INNER JOIN permissions p ON p.`key` IN ('tasks.view','tasks.create','tasks.update','messages.view','messages.create')
WHERE r.slug = 'agent';

INSERT IGNORE INTO role_permissions (role_id, permission_id, created_at)
SELECT r.id, p.id, NOW()
FROM roles r
INNER JOIN permissions p ON p.`key` IN ('tasks.view','messages.view')
WHERE r.slug = 'sub_agent';
