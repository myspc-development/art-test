import { useEffect, useState } from 'react';

interface WidgetDef {
  id: string;
  roles?: string[];
}

export default function useFilteredWidgets(widgets: WidgetDef[], currentUser: {role: string}) {
  const [widgetRoles, setWidgetRoles] = useState<Record<string, string[]>>({});
  const restRoot = window.wpApiSettings?.root || '/wp-json/';
  const nonce = window.wpApiSettings?.nonce || '';

  useEffect(() => {
    fetch(restRoot + 'artpulse/v1/dashboard-config', { headers: { 'X-WP-Nonce': nonce } })
      .then(r => r.json())
      .then(data => setWidgetRoles(data.widget_roles || {}))
      .catch(() => {});
  }, []);

  return widgets.filter(widget => {
    const allowed = widgetRoles[widget.id] || widget.roles || [];
    return allowed.includes(currentUser.role);
  });
}

