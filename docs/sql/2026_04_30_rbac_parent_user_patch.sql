-- RBAC + Sub-Agent ownership patch
-- Run once on existing installations

ALTER TABLE users
  ADD COLUMN parent_user_id BIGINT UNSIGNED NULL AFTER tenant_id;

ALTER TABLE users
  ADD CONSTRAINT fk_users_parent
  FOREIGN KEY (parent_user_id) REFERENCES users(id)
  ON DELETE SET NULL;

-- Optional: ensure finance.view is not granted to agent/sub_agent by default
DELETE rp
FROM role_permissions rp
INNER JOIN roles r ON r.id = rp.role_id
INNER JOIN permissions p ON p.id = rp.permission_id
WHERE r.slug IN ('agent', 'sub_agent')
  AND p.`key` = 'finance.view';

-- Add missing permissions for Tasks/Messages/Finance management
INSERT IGNORE INTO permissions (`key`, name, group_key) VALUES
('finance.update', 'Manage Finance', 'finance'),
('tasks.view', 'View Tasks', 'tasks'),
('tasks.create', 'Create Tasks', 'tasks'),
('tasks.update', 'Update Tasks', 'tasks'),
('tasks.delete', 'Delete Tasks', 'tasks'),
('messages.view', 'View Messages', 'messages'),
('messages.create', 'Create Messages', 'messages');

-- Default grants for Admin
INSERT IGNORE INTO role_permissions (role_id, permission_id, created_at)
SELECT r.id, p.id, NOW()
FROM roles r
INNER JOIN permissions p ON p.`key` IN ('finance.view','finance.update','tasks.view','tasks.create','tasks.update','tasks.delete','messages.view','messages.create')
WHERE r.slug = 'admin';

-- Default grants for Agent (no finance)
INSERT IGNORE INTO role_permissions (role_id, permission_id, created_at)
SELECT r.id, p.id, NOW()
FROM roles r
INNER JOIN permissions p ON p.`key` IN ('tasks.view','tasks.create','tasks.update','messages.view')
WHERE r.slug = 'agent';

-- Default grants for Sub-Agent (view only)
INSERT IGNORE INTO role_permissions (role_id, permission_id, created_at)
SELECT r.id, p.id, NOW()
FROM roles r
INNER JOIN permissions p ON p.`key` IN ('tasks.view','messages.view')
WHERE r.slug = 'sub_agent';
