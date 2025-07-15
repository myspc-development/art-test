import React from 'react';
import { rolesMenus } from './rolesMenus';
import {
  Home,
  User,
  Calendar,
  Heart,
  MessageCircle,
  Palette,
  Image,
  Plus,
  Folder,
  Building2,
  Edit,
  Users,
  UserPlus,
  LayoutGrid
} from 'lucide-react';

const icons = {
  Home,
  User,
  Calendar,
  Heart,
  MessageCircle,
  Palette,
  Image,
  Plus,
  Folder,
  Building2,
  Edit,
  Users,
  UserPlus,
  LayoutGrid
};

export default function SidebarMenu({ role, activeSection, setActiveSection }) {
  const menu = rolesMenus[role] || [];

  return (
    <nav className="ap-dashboard-sidebar" aria-label="Dashboard sections">
      <ul role="menu">
        {menu.map(item => {
          const Icon = icons[item.icon] || (() => null);
          const active = item.section === activeSection;
          return (
            <li key={item.section} role="none">
              <button
                type="button"
                role="menuitem"
                aria-current={active ? 'page' : undefined}
                onClick={() => setActiveSection(item.section)}
                className={`ap-sidebar-link ${active ? 'active' : ''}`}
              >
                <Icon className="ap-icon" />
                <span>{item.label}</span>
              </button>
            </li>
          );
        })}
      </ul>
    </nav>
  );
}
