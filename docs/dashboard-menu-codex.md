---
title: Dashboard Menu Codex
category: widgets
role: developer
last_updated: 2025-07-20
status: draft
---

# Dashboard Menu Codex

This guide explains how sidebar Menu Nodes are defined for the React dashboards.

## 1. Purpose of `rolesMenus.js`

`assets/js/rolesMenus.js` exports a `rolesMenus` object mapping user roles to arrays of menu nodes. Each node contains a `label`, a `lucide-react` icon name, and a `section` key used to toggle dashboard modules. `<SidebarMenu>` reads this object to build the navigation for the current role.

Icon components are imported from `lucide-react` as ES modules and bundled with the dashboard scripts.

## 2. Adding Roles and Menu Nodes

To add a new role or modify the menu for an existing role, edit `rolesMenus.js`:

```js
export const rolesMenus = {
  curator: [
    { label: 'Curator Dashboard', icon: 'LayoutGrid', section: 'dashboard' },
    { label: 'Submissions', icon: 'Inbox', section: 'submissions' }
  ],
  member: [
    { label: 'Dashboard', icon: 'Home', section: 'dashboard' },
    { label: 'My Profile', icon: 'User', section: 'profile' }
  ]
};
```

Each role array can include any number of nodes. Use icon names from `lucide-react` and choose section IDs that match your dashboard markup.

## 3. `<SidebarMenu>` Example

`SidebarMenu.jsx` outputs markup styled to match the Salient theme.

```jsx
import { useState } from 'react';
import SidebarMenu from './SidebarMenu';
import { rolesMenus } from './rolesMenus';

function DashboardExample({ role }) {
  const [activeSection, setActiveSection] = useState(
    rolesMenus[role]?.[0]?.section || ''
  );

  return (
    <div className="flex gap-6">
      <SidebarMenu
        role={role}
        activeSection={activeSection}
        setActiveSection={setActiveSection}
      />
      <div className="flex-1">
        {/* sections go here */}
      </div>
    </div>
  );
}
```

The sidebar `<nav>` highlights the active Menu Node and toggles dashboard modules accordingly.

## 4. Verifying Role Menu Entries

If links disappear from the dashboard, confirm that `rolesMenus.js` defines arrays for **artist**, **member** and **organization**. After editing, run:

```bash
npm run build
```

This regenerates the production JavaScript files.