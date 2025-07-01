import React from 'react';
import { rolesMenus } from './rolesMenus';
import * as Icons from 'lucide-react';

export default function SidebarMenu({ role, activeSection, setActiveSection }) {
  const menu = rolesMenus[role] || [];

  return (
    <nav className="w-full md:w-60 bg-white shadow-md py-6 px-3 rounded-2xl">
      <ul className="space-y-2">
        {menu.map(item => {
          const Icon = Icons[item.icon] || (() => null);
          const active = item.section === activeSection;
          return (
            <li key={item.section}>
              <button
                type="button"
                onClick={() => setActiveSection(item.section)}
                className={`w-full flex items-center gap-2 px-3 py-2 rounded-xl text-left ${active ? 'bg-indigo-100 font-medium' : 'hover:bg-indigo-50'}`}
              >
                <Icon className="w-4 h-4" />
                <span>{item.label}</span>
              </button>
            </li>
          );
        })}
      </ul>
    </nav>
  );
}
