---
title: Organization Roles Matrix
category: developer
role: developer
last_updated: 2025-07-20
status: draft
---
# Organization Roles Matrix

The roles matrix maps users to organizations with a single role per row. Roles are stored in the `ap_org_user_roles` table which is created on plugin activation.

```sql
CREATE TABLE ap_org_user_roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  org_id INT NOT NULL,
  user_id BIGINT NOT NULL,
  role ENUM('admin','editor','curator','promoter') DEFAULT 'editor',
  status ENUM('active','pending','invited') DEFAULT 'active',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY org_user (org_id, user_id)
);
```

Use `MultiOrgRoles::assign_roles()` and `MultiOrgRoles::get_user_roles()` to manage assignments. To check capabilities per org call `ap_user_has_org_capability()`.

## Updating

When upgrading from versions prior to 1.4.0 the plugin's update routine runs `create_monetization_tables()` followed by `MultiOrgRoles::maybe_install_table()`. This ensures the `ap_org_user_roles` table is installed even on existing sites.

💬 Found something outdated? Submit Feedback
