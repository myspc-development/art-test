import React, { useEffect, useState } from 'react';
const { __ } = wp.i18n;

interface Widget { id: string; name: string; roles?: string[]; }
// REST responses use `widget_roles` for the matrix object
interface Matrix { [key: string]: string[]; }

export default function AdminWidgetMatrix() {
  const [widgets, setWidgets] = useState<Widget[]>([]);
  const [roles, setRoles] = useState<string[]>([]);
  const [matrix, setMatrix] = useState<Matrix>({});
  const [error, setError] = useState('');
  const restRoot =
    window.APWidgetMatrix?.root || window.wpApiSettings?.root || '/wp-json/';
  const nonce =
    window.APWidgetMatrix?.nonce || window.wpApiSettings?.nonce || '';

  const load = () => {
    setError('');
    Promise.all([
      fetch(restRoot + 'artpulse/v1/widgets').then(r => r.json()),
      fetch(restRoot + 'artpulse/v1/roles').then(r => r.json()),
      fetch(restRoot + 'artpulse/v1/dashboard-config', {
        headers: { 'X-WP-Nonce': nonce }
      }).then(r => r.json())
    ])
      .then(([widgetsData, rolesData, config]) => {
        setWidgets(widgetsData);
        setRoles(config.role_widgets ? Object.keys(config.role_widgets) : rolesData);
        setMatrix(config.widget_roles || {});
      })
      .catch(() => {
        setError(__('Unable to load data. Please try again.', 'artpulse'));
      });
  };

  useEffect(load, []);

  const toggle = (wid: string, role: string) => {
    setMatrix(m => {
      const list = new Set(m[wid] || []);
      if (list.has(role)) list.delete(role);
      else list.add(role);
      return { ...m, [wid]: Array.from(list) };
    });
  };

  const save = () => {
    fetch(restRoot + 'artpulse/v1/dashboard-config', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
      body: JSON.stringify({ widget_roles: matrix })
    }).then(() => {
      if (window.wp?.data?.dispatch) {
        wp.data.dispatch('core/notices').createNotice('success', __('Saved', 'artpulse'), { isDismissible: true });
      }
    });
  };

  if (error) {
    return (
      <div className="notice notice-error inline">
        <p>{error}</p>
        <p>
          <button type="button" className="button" onClick={load}>
            {__('Retry', 'artpulse')}
          </button>
        </p>
      </div>
    );
  }

  if (widgets.length === 0) {
    return (
      <div className="notice notice-info inline">
        <p>{__('No widgets available.', 'artpulse')}</p>
      </div>
    );
  }

  return (
    <div>
      <table className="widefat striped">
        <thead>
          <tr>
            <th>{__('Widget', 'artpulse')}</th>
            {roles.map(r => (
              <th key={r}>{r}</th>
            ))}
          </tr>
        </thead>
        <tbody>
          {widgets.map(w => (
            <tr key={w.id}>
              <td>{w.name || w.id}</td>
              {roles.map(role => (
                <td key={role} style={{ textAlign: 'center' }}>
                  <input
                    type="checkbox"
                    checked={(matrix[w.id] || w.roles || []).includes(role)}
                    onChange={() => toggle(w.id, role)}
                  />
                </td>
              ))}
            </tr>
          ))}
        </tbody>
      </table>
      <p>
        <button type="button" className="button button-primary" onClick={save}>
          {__('Save', 'artpulse')}
        </button>
      </p>
    </div>
  );
}
