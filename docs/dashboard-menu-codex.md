# Dashboard Menu Codex

This guide explains how sidebar menus are defined for the React dashboards.

## 1. Purpose of `rolesMenus.js`

`assets/js/rolesMenus.js` exports a `rolesMenus` object mapping user roles to arrays of menu items. Each item contains a `label`, a `lucide-react` icon name, and a `section` key used to toggle dashboard content. `<SidebarMenu>` reads this object to build the navigation for the current role.

## 2. Adding Roles and Menu Items

To add a new role or modify the menu for an existing role, edit `rolesMenus.js`:

```js
export const rolesMenus = {
  ...,
  curator: [
    { label: 'Curator Dashboard', icon: 'LayoutGrid', section: 'dashboard' },
    { label: 'Submissions', icon: 'Inbox', section: 'submissions' }
  ]
};
```

Each role array can include any number of items. Use icon names from `lucide-react` and choose section IDs that match your dashboard markup.

## 3. `<SidebarMenu>` Example

`SidebarMenu.jsx` comes with Tailwind classes for layout and highlighting. Use it inside a dashboard layout like this:
See also the [Tailwind Design Guide](tailwind-design-guide.md) for consistent layout utilities.

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

The sidebar `<nav>` uses classes like `bg-white`, `shadow-md`, `py-6`, `px-3` and `rounded-2xl`. Menu buttons highlight the active section using conditional classes.
