import React, { useState, useEffect, useRef } from 'react';
import ReactDOM from 'react-dom';

function WidgetsEditor({ widgets, config, roles, nonce, ajaxUrl }) {
  const roleKeys = Object.keys(roles);
  const [activeRole, setActiveRole] = useState(roleKeys[0] || '');
  const [active, setActive] = useState([]);
  const [available, setAvailable] = useState([]);
  const activeRef = useRef(null);
  const availRef = useRef(null);

  useEffect(() => {
    const activeIds = config[activeRole] || [];
    const activeWidgets = widgets.filter(w => activeIds.includes(w.id));
    const availWidgets = widgets.filter(w => !activeIds.includes(w.id));
    setActive(activeWidgets);
    setAvailable(availWidgets);
  }, [activeRole]);

  useEffect(() => {
    if (typeof Sortable === 'undefined') return;
    if (!activeRef.current || !availRef.current) return;
    const opts = {
      group: 'widgets',
      animation: 150,
      onSort: updateFromDom,
      onAdd: updateFromDom,
      onRemove: updateFromDom,
    };
    const act = Sortable.create(activeRef.current, opts);
    const avail = Sortable.create(availRef.current, opts);
    return () => {
      act.destroy();
      avail.destroy();
    };
  }, [activeRole]);

  function updateFromDom() {
    if (!activeRef.current || !availRef.current) return;
    const idsFrom = ul => Array.from(ul.querySelectorAll('li')).map(li => li.dataset.id);
    const actIds = idsFrom(activeRef.current);
    const availIds = idsFrom(availRef.current);
    setActive(actIds.map(id => widgets.find(w => w.id === id)));
    setAvailable(availIds.map(id => widgets.find(w => w.id === id)));
  }

  function handleSave() {
    const form = new FormData();
    form.append('action', 'ap_save_dashboard_widget_config');
    form.append('nonce', nonce);
    active.forEach(w => form.append(`config[${activeRole}][]`, w.id));
    fetch(ajaxUrl, { method: 'POST', body: form })
      .then(r => r.json())
      .then(() => alert('Saved'));
  }

  return (
    <div className="ap-widgets-editor">
      <select value={activeRole} onChange={e => setActiveRole(e.target.value)}>
        {roleKeys.map(r => (
          <option key={r} value={r}>{roles[r].name || r}</option>
        ))}
      </select>
      <div className="ap-widgets-columns">
        <div className="ap-widgets-available">
          <h4>Available Widgets</h4>
          <ul ref={availRef}>
            {available.map(w => (
              <li key={w.id} data-id={w.id}>{w.name}</li>
            ))}
          </ul>
        </div>
        <div className="ap-widgets-active">
          <h4>Active Widgets</h4>
          <ul ref={activeRef}>
            {active.map(w => (
              <li key={w.id} data-id={w.id}>{w.name}</li>
            ))}
          </ul>
        </div>
      </div>
      <button onClick={handleSave}>Save</button>
    </div>
  );
}

document.addEventListener('DOMContentLoaded', () => {
  const el = document.getElementById('ap-dashboard-widgets-canvas');
  if (el && window.APDashboardWidgetsEditor) {
    ReactDOM.render(
      <WidgetsEditor {...APDashboardWidgetsEditor} />,
      el
    );
  }
});
