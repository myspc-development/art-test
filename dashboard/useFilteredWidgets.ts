import { useEffect, useState } from 'react';

interface WidgetDef {
  id: string;
  roles?: string[];
  restOnly?: boolean;
}

interface CurrentUser {
  role?: string;
  roles?: string[];
}

export default function useFilteredWidgets(widgets: WidgetDef[], currentUser: CurrentUser) {
  const [widgetRoles, setWidgetRoles] = useState<Record<string, string[]>>({});
  const restRoot = window.wpApiSettings?.root || '/wp-json/';
  const nonce = window.wpApiSettings?.nonce || '';

  useEffect(() => {
    const fetchConfig = () => {
      fetch(restRoot + 'artpulse/v1/dashboard-config', { headers: { 'X-WP-Nonce': nonce } })
        .then(r => r.json())
        .then(data => setWidgetRoles(data.widget_roles || {}))
        .catch(err => {
          // Log the error for debugging purposes
          console.error('Failed to load dashboard configuration', err);
          // Offer a retry option and show a fallback message
          if (window.confirm('Failed to load dashboard configuration. Retry?')) {
            fetchConfig();
          } else {
            alert('Using default dashboard configuration.');
          }
        });
    };
    fetchConfig();
  }, []);

  const roles = currentUser.roles || (currentUser.role ? [currentUser.role] : []);

  const filtered = widgets.filter(widget => {
    const allowed = widgetRoles[widget.id] || widget.roles || [];
    if (!roles.length) return true;
    return roles.some(r => allowed.includes(r));
  });

  const restOnly = Object.entries(widgetRoles)
    .filter(([id]) => !widgets.some(w => w.id === id))
    .filter(([, allowed]) => {
      if (!roles.length) return true;
      return roles.some(r => allowed.includes(r));
    })
    .map(([id]) => ({ id, restOnly: true }));

  return [...filtered, ...restOnly];
}

