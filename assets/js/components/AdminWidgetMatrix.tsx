import React, { useEffect, useState } from 'react';
const { __ } = wp.i18n;

interface Widget { id: string; name: string; roles?: string[]; }
// REST responses use `widget_roles` for the matrix object
interface Matrix { [key: string]: string[]; }

export default function AdminWidgetMatrix() {
  const [widgets, setWidgets] = useState<Widget[]>([]);
  const [roles, setRoles] = useState<string[]>([]);
  const [matrix, setMatrix] = useState<Matrix>({});
  const [roleWidgets, setRoleWidgets] = useState<Matrix>({});
  const [locked, setLocked] = useState<string[]>([]);
  const [filterRole, setFilterRole] = useState('all');
  const [search, setSearch] = useState('');
  const [error, setError] = useState('');
  const endpoint =
    window.APWidgetMatrix?.endpoint ||
    (window.wpApiSettings?.root || '/wp-json/') +
      'artpulse/v1/dashboard-config';
  const restRoot = endpoint.replace(/artpulse\/v1\/dashboard-config\/?$/, '');
  const nonce =
    window.APWidgetMatrix?.nonce || window.wpApiSettings?.nonce || '';
  const apNonce = window.APWidgetMatrix?.apNonce || '';

  const load = () => {
    setError('');
    Promise.all([
      fetch(restRoot + 'artpulse/v1/widgets').then(r => r.json()),
      fetch(restRoot + 'artpulse/v1/roles').then(r => r.json()),
      fetch(endpoint, {
        headers: { 'X-WP-Nonce': nonce, 'X-AP-Nonce': apNonce }
      }).then(r => r.json())
    ])
      .then(([widgetsData, rolesData, config]) => {
        setWidgets(widgetsData);
        const layoutMap = config.layout || config.role_widgets || {};
        const roleList = config.layout
          ? Object.keys(config.layout)
          : config.role_widgets
          ? Object.keys(config.role_widgets)
          : rolesData;
        setRoles(roleList);
        setMatrix(config.widget_roles || {});
        setRoleWidgets(layoutMap);
        setLocked(config.locked || []);
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
    fetch(endpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': nonce,
        'X-AP-Nonce': apNonce
      },
      body: JSON.stringify({
        widget_roles: matrix,
        layout: roleWidgets,
        locked
      })
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

  const filtered = widgets.filter(w => {
    const q = search.toLowerCase();
    const matchesSearch = !q || (w.name || '').toLowerCase().includes(q) || w.id.includes(q);
    const matchesRole = filterRole === 'all' || (matrix[w.id] || w.roles || []).includes(filterRole);
    return matchesSearch && matchesRole;
  });

  return (
    <div>
      <div style={{ marginBottom: '1em', display: 'flex', gap: '1em' }}>
        <select value={filterRole} onChange={e => setFilterRole(e.target.value)}>
          <option value="all">{__('All Roles', 'artpulse')}</option>
          {roles.map(r => (
            <option key={r} value={r}>
              {r}
            </option>
          ))}
        </select>
        <input
          type="search"
          placeholder={__('Search widgets…', 'artpulse')}
          value={search}
          onChange={e => setSearch(e.target.value)}
        />
      </div>
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
          {filtered.map(w => (
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
