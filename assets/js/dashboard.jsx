import React, { useState } from 'react';
import ReactDOM from 'react-dom';
import SidebarMenu from './SidebarMenu';
import { rolesMenus } from './rolesMenus';

function DashboardApp({ role }) {
  const [activeSection, setActiveSection] = useState(
    rolesMenus[role]?.[0]?.section || ''
  );

  return (
    <div className="ap-dashboard-react flex gap-6">
      <SidebarMenu
        role={role}
        activeSection={activeSection}
        setActiveSection={setActiveSection}
      />
      <div className="flex-1">
        {(rolesMenus[role] || []).map((item) => (
          <section
            key={item.section}
            style={{ display: activeSection === item.section ? 'block' : 'none' }}
          >
            <div id={`ap-${item.section}`}></div>
          </section>
        ))}
      </div>
    </div>
  );
}

document.addEventListener('DOMContentLoaded', () => {
  const root = document.getElementById('ap-dashboard-root');
  if (root) {
    ReactDOM.render(<DashboardApp role={APDashboard.role} />, root);
  }
});
