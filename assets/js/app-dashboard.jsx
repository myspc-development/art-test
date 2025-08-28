import React, { useEffect, useState } from 'react';
import { createRoot } from 'react-dom/client';
import DashboardNavbar from './components/DashboardNavbar';
import MessagesPanel from './components/MessagesPanel';
import CommunityAnalyticsPanel from './components/CommunityAnalyticsPanel';
import DashboardContainer from './DashboardContainer.jsx';
import '../css/main.css';

function AppDashboard() {
  const [role, setRole] = useState(null);
  const apiRoot = window.ArtPulseDashboardApi?.apiUrl || window.ArtPulseDashboardApi?.root || '/wp-json/';
  const nonce = window.apNonce || window.ArtPulseDashboardApi?.nonce || '';
  const token = window.ArtPulseDashboardApi?.apiToken || '';

  useEffect(() => {
    const headers = { 'X-WP-Nonce': nonce };
    if (token) headers['Authorization'] = `Bearer ${token}`;
    fetch(`${apiRoot}artpulse/v1/me`, {
      headers,
      credentials: 'same-origin'
    })
      .then(res => (res.status === 401 || res.status === 403 || res.status === 404 ? {} : res.json()))
      .then(data => setRole(data.role))
      .catch(() => setRole(null));
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
  const isV2 = rootEl?.dataset.apV2 === '1';
  if (rootEl && isV2) {
    const root = createRoot(rootEl);
    root.render(<AppDashboard />);
  }
});

// Expose for debugging when loaded directly
window.APDashboardApp = AppDashboard;

