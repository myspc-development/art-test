import React from 'react';
import { createRoot } from 'react-dom/client';
import RoleDashboard from './components/RoleDashboard';

/**
 * Bootstraps the RoleDashboard React component.
 *
 * The [ap_user_dashboard] shortcode outputs a <div id="ap-dashboard-root">
 * container. This script locates that element and mounts the dashboard when
 * present so pages embedding the shortcode automatically load the React UI.
 */
document.addEventListener('DOMContentLoaded', () => {
  const el = document.getElementById('ap-dashboard-root');
  if (el) {
    const root = createRoot(el);
    root.render(<RoleDashboard />);
  }
});
