import React, { useState } from 'react';
import ReactDOM from 'react-dom';
import SidebarMenu from './SidebarMenu';
import { rolesMenus } from './rolesMenus';

function DashboardApp({ role }) {
  const [activeSection, setActiveSection] = useState(
    rolesMenus[role]?.[0]?.section || ''
  );

  return (
    <div className="ap-dashboard-wrapper">
      <SidebarMenu
        role={role}
        activeSection={activeSection}
        setActiveSection={setActiveSection}
      />
      <div className="ap-dashboard-main">
        {(rolesMenus[role] || []).map((item) => (
          <section
            key={item.section}
            id={`ap-${item.section}`}
            style={{ display: activeSection === item.section ? 'block' : 'none' }}
          >
            {item.section === 'messages' ? (
              <div className="ap-messages">
                <div className="ap-conversations">
                  <h3>Conversations</h3>
                  <ul id="ap-conversation-list"></ul>
                </div>
                <div className="ap-thread">
                  <ul id="ap-message-list" aria-live="polite"></ul>
                  <form id="ap-message-form" style={{ display: 'none' }}>
                    <input type="hidden" name="recipient_id" value="" />
                    <label htmlFor="ap-message-content">Message</label>
                    <textarea id="ap-message-content" name="content" required></textarea>
                    <button type="submit">Send</button>
                  </form>
                </div>
              </div>
            ) : (
              <div id={`ap-${item.section}`}></div>
            )}
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
