import React, { useEffect, useState } from 'react';

interface Widget { id: string; name: string; roles?: string[]; }
// REST responses use `widget_roles` for the matrix object
interface Matrix { [key: string]: string[]; }

export default function AdminWidgetMatrix() {
  const [widgets, setWidgets] = useState<Widget[]>([]);
  const [roles, setRoles] = useState<string[]>([]);
  const [matrix, setMatrix] = useState<Matrix>({});
  const restRoot =
    window.APWidgetMatrix?.root || window.wpApiSettings?.root || '/wp-json/';
  const nonce =
    window.APWidgetMatrix?.nonce || window.wpApiSettings?.nonce || '';

  useEffect(() => {
    fetch(restRoot + 'artpulse/v1/widgets')
      .then(r => r.json())
      .then(setWidgets);
    fetch(restRoot + 'artpulse/v1/roles')
      .then(r => r.json())
      .then(setRoles);
    fetch(restRoot + 'artpulse/v1/dashboard-config', {
      headers: { 'X-WP-Nonce': nonce }
    })
      .then(r => r.json())
      .then(data => setMatrix(data.widget_roles || {}));
  }, []);

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
        wp.data.dispatch('core/notices').createNotice('success', 'Saved', { isDismissible: true });
      }
    });
  };

  return (
    <div>
      <table className="widefat striped">
        <thead>
          <tr>
            <th>Widget</th>
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
          Save
        </button>
      </p>
    </div>
  );
}
