import React from 'react';
import { createRoot } from 'react-dom/client';
import RoleDashboard, { Role } from './RoleDashboard';

const bootstrap = () => {
  const el = document.getElementById('ap-dashboard-root');
  if (!el) return;
  const roleAttr = el.getAttribute('data-role');
  const data = (window as any).apDashboardData;
  const role = (roleAttr || data?.role || 'member') as Role;
  const root = createRoot(el);
  root.render(<RoleDashboard role={role} />);
};

document.addEventListener('DOMContentLoaded', bootstrap);
document.addEventListener('ap-dashboard-bootstrap', bootstrap);

