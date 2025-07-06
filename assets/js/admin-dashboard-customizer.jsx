import React, { useState } from 'react';
import ReactDOM from 'react-dom';

function WidgetConfig({ widgets, config, roles, nonce, ajaxUrl }) {
  const roleKeys = Object.keys(roles);
  const [activeRole, setActiveRole] = useState(roleKeys[0] || '');
  const [layout, setLayout] = useState(config[activeRole] || widgets.map(w => w.id));

  function handleSave() {
    const form = new FormData();
    form.append('action', 'ap_save_dashboard_widget_config');
    form.append('nonce', nonce);
    layout.forEach(id => form.append(`config[${activeRole}][]`, id));
    fetch(ajaxUrl, { method: 'POST', body: form })
      .then(r => r.json())
      .then(() => alert('Saved'));
  }

  function toggle(id) {
    setLayout(l => l.includes(id) ? l.filter(w => w !== id) : [...l, id]);
  }

  return (
    <div className="ap-dashboard-customizer">
      <select value={activeRole} onChange={e => {
        const role = e.target.value; setActiveRole(role); setLayout(config[role] || widgets.map(w => w.id));
      }}>
        {roleKeys.map(r => <option key={r} value={r}>{roles[r].name || r}</option>)}
      </select>
      <ul>
        {widgets.map(w => (
          <li key={w.id}>
            <label>
              <input type="checkbox" checked={layout.includes(w.id)} onChange={() => toggle(w.id)} />
              {w.name}
            </label>
          </li>
        ))}
      </ul>
      <button onClick={handleSave}>Save</button>
    </div>
  );
}

document.addEventListener('DOMContentLoaded', () => {
  const el = document.getElementById('ap-dashboard-widgets-admin');
  if (el && window.APDashboardCustomizer) {
    ReactDOM.render(
      <WidgetConfig {...APDashboardCustomizer} />,
      el
    );
  }
});
