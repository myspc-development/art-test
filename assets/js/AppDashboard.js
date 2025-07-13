import React, { useEffect, useState } from 'react';
import ReactDOM from 'react-dom';
import DashboardNavbar from './components/DashboardNavbar';
import MessagesPanel from './components/MessagesPanel';

export default function AppDashboard() {
  const [role, setRole] = useState(null);

  useEffect(() => {
    fetch('/wp-json/artpulse/v1/me')
      .then(res => {
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return res.json();
      })
      .then(data => setRole(data.role))
      .catch(err => console.error('Profile fetch error:', err));
  }, []);

  const logout = () => (window.location.href = '/wp-login.php?action=logout');

  return (
    <div className="min-h-screen bg-gray-100">
      <DashboardNavbar userRole={role} onLogout={logout} />
      <main className="p-4">
        {/* Render panels conditionally here */}
        <MessagesPanel />
      </main>
    </div>
  );
}

document.addEventListener('DOMContentLoaded', () => {
  const rootEl = document.getElementById('ap-dashboard-root');
  if (rootEl && window.ReactDOM) {
    ReactDOM.render(<AppDashboard />, rootEl);
  }
});
