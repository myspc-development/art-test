# Dashboard Menu Codex

This guide explains how sidebar menus are defined for the React dashboards.

## 1. Purpose of `rolesMenus.js`

`assets/js/rolesMenus.js` exports a `rolesMenus` object mapping user roles to arrays of menu items. Each item contains a `label`, a `lucide-react` icon name, and a `section` key used to toggle dashboard content. `<SidebarMenu>` reads this object to build the navigation for the current role.

Icon components are imported from `lucide-react` as ES modules and bundled with the dashboard scripts, so no global icon script is needed.

## 2. Adding Roles and Menu Items

To add a new role or modify the menu for an existing role, edit `rolesMenus.js`:

```js
export const rolesMenus = {
  ...,
  curator: [
    { label: 'Curator Dashboard', icon: 'LayoutGrid', section: 'dashboard' },
    { label: 'Submissions', icon: 'Inbox', section: 'submissions' }
  ],
  member: [
    { label: 'Dashboard', icon: 'Home', section: 'dashboard' },
    { label: 'My Profile', icon: 'User', section: 'profile' },
    { label: 'Events', icon: 'Calendar', section: 'events' },
    { label: 'Favorites', icon: 'Heart', section: 'favorites' },
    { label: 'Messages', icon: 'MessageCircle', section: 'messages' },
    { label: 'Forum', icon: 'MessageCircle', section: 'forum' }
  ]
};
```

Each role array can include any number of items. Use icon names from `lucide-react` and choose section IDs that match your dashboard markup.

## 3. `<SidebarMenu>` Example

`SidebarMenu.jsx` outputs markup styled to match the Salient theme. Use it inside a WPBakery row or column like this:

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

The sidebar `<nav>` inherits panel styles from Salient and highlights the active section by toggling a class on each menu button.

## 4. Verifying Role Menu Entries

If links disappear from the dashboard, confirm that `assets/js/rolesMenus.js` defines arrays for **artist**, **member** and **organization**. Each array should list the sidebar items you expect for that role:

```js
export const rolesMenus = {
  artist: [/* ... */],
  member: [/* ... */],
  organization: [/* ... */]
};
```

Add or restore missing menu objects to the appropriate array and save the file.

After updating `rolesMenus.js` rebuild the bundled scripts so WordPress picks up the changes:

```bash
npm run build
```

Run the command from the plugin root to regenerate the production JavaScript files.
