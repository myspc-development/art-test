import React, { useEffect, useState } from 'react';
import { createRoot } from 'react-dom/client';
import DashboardNavbar from './components/DashboardNavbar';
import MessagesPanel from './components/MessagesPanel';
import CommunityAnalyticsPanel from './components/CommunityAnalyticsPanel';
import DashboardContainer from './DashboardContainer.jsx';

function AppDashboard() {
  const [role, setRole] = useState(null);
  const apiRoot = window.ArtPulseDashboardApi?.root || '/wp-json/';
  const nonce = window.apNonce || window.ArtPulseDashboardApi?.nonce || '';

  useEffect(() => {
    fetch(`${apiRoot}artpulse/v1/me`, {
      headers: { 'X-WP-Nonce': nonce },
      credentials: 'same-origin'
    })
      .then(res => res.json())
      .then(data => setRole(data.role));
  }, []);

  const logout = () => (window.location.href = '/wp-login.php?action=logout');

  return (
    <div className="min-h-screen bg-gray-100">
      <DashboardNavbar userRole={role} onLogout={logout} />
      <main className="p-4">
        <DashboardContainer role={role} />
        <MessagesPanel />
        <CommunityAnalyticsPanel />
      </main>
    </div>
  );
}

document.addEventListener('DOMContentLoaded', () => {
  const rootEl = document.getElementById('ap-dashboard-root');
  if (rootEl) {
    const root = createRoot(rootEl);
    root.render(<AppDashboard />);
  }
});

// Expose for debugging when loaded directly
window.APDashboardApp = AppDashboard;

