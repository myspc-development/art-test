import React from 'react';
import ReactDOM from 'react-dom';
import RoleDashboard from './components/RoleDashboard';

/**
 * Bootstraps the RoleDashboard React component.
 *
 * The [ap_user_dashboard] shortcode outputs a <div id="ap-user-dashboard">
 * container. This script locates that element and mounts the dashboard when
 * present so pages embedding the shortcode automatically load the React UI.
 */
document.addEventListener('DOMContentLoaded', () => {
  const el = document.getElementById('ap-user-dashboard');
  if (el) {
    ReactDOM.render(<RoleDashboard />, el);
  }
});
