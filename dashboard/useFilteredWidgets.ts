import { useEffect, useState } from 'react';

interface WidgetDef {
  id: string;
  roles?: string[];
  restOnly?: boolean;
  capability?: string;
  excluded_roles?: string[];
}

interface CurrentUser {
  role?: string;
  roles?: string[];
  capabilities?: string[];
}

export default function useFilteredWidgets(widgets: WidgetDef[], currentUser: CurrentUser) {
  const [widgetRoles, setWidgetRoles] = useState<Record<string, string[]>>({});
  const [caps, setCaps] = useState<Record<string, string>>({});
  const [excluded, setExcluded] = useState<Record<string, string[]>>({});
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState<boolean>(true);
  const restRoot = (window as any).wpApiSettings?.root || '/wp-json/';
  const nonce = (window as any).wpApiSettings?.nonce || '';

  const fetchConfig = () => {
    const controller = new AbortController();
    setLoading(true);
    fetch(restRoot + 'artpulse/v1/dashboard-config', {
      headers: { 'X-WP-Nonce': nonce },
      signal: controller.signal,
    })
      .then(r => {
        if (!r.ok) {
          throw new Error(`Failed to load dashboard configuration: ${r.status}`);
        }
        return r.json();
      })
      .then(data => {
        setWidgetRoles(data.widget_roles || {});
        setCaps(data.capabilities || {});
        setExcluded(data.excluded_roles || {});
        setError(null);
      })
      .catch(err => {
        if (err.name !== 'AbortError') {
          console.error('Failed to load dashboard configuration', err);
          setError(err.message || 'Failed to load dashboard configuration.');
        }
      })
      .finally(() => setLoading(false));
    return controller;
  };

  useEffect(() => {
    const controller = fetchConfig();
    return () => controller.abort();
  }, [restRoot, nonce]);

  const roles = currentUser.roles || (currentUser.role ? [currentUser.role] : []);
  const userCaps = currentUser.capabilities || [];

  const filtered = widgets.filter(widget => {
    const allowed = widgetRoles[widget.id] || widget.roles || [];
    const cap = caps[widget.id] || widget.capability;
    const deny = excluded[widget.id] || widget.excluded_roles || [];
    if (cap && !userCaps.includes(cap)) return false;
    if (roles.some(r => deny.includes(r))) return false;
    if (allowed.length === 0) return true;
    if (!roles.length) return true;
    return roles.some(r => allowed.includes(r));
  });

  const restOnly = Object.entries(widgetRoles)
    .filter(([id]) => !widgets.some(w => w.id === id))
    .filter(([id, allowed]) => {
      const cap = caps[id];
      const deny = excluded[id] || [];
      if (cap && !userCaps.includes(cap)) return false;
      if (roles.some(r => deny.includes(r))) return false;
      if (allowed.length === 0) return true;
      if (!roles.length) return true;
      return roles.some(r => allowed.includes(r));
    })
    .map(([id]) => ({ id, restOnly: true }));

  return { widgets: [...filtered, ...restOnly], error, retry: fetchConfig, loading };
}

