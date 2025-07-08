import React, { useState, useEffect, useRef } from 'react';
import { createRoot } from 'react-dom/client';

if (!window.APDashboardWidgetsEditor || !window.APDashboardWidgetsEditor.config) {
  console.error(
    'APDashboardWidgetsEditor.config is missing; initializing empty layout.'
  );
  window.APDashboardWidgetsEditor = {
    ...window.APDashboardWidgetsEditor,
    config: {},
  };
}

if (!window.APDashboardWidgetsEditor || !window.APDashboardWidgetsEditor.roles) {
  console.error('APDashboardWidgetsEditor.roles is missing; using empty roles.');
  window.APDashboardWidgetsEditor = {
    ...window.APDashboardWidgetsEditor,
    roles: {},
  };
}

function WidgetSettingsForm({ id, onClose, l10n = {} }) {
  const [schema, setSchema] = useState([]);
  const [values, setValues] = useState({});
  const [error, setError] = useState(false);
  const restRoot = (window.wpApiSettings && window.wpApiSettings.root) || '';
  const restNonce = (window.wpApiSettings && window.wpApiSettings.nonce) || '';

  useEffect(() => {
    if (!id) return;
    setError(false);
    fetch(`${restRoot}artpulse/v1/widget-settings/${id}`, {
      headers: { 'X-WP-Nonce': restNonce }
    })
      .then(r => {
        if (!r.ok) throw new Error('Request failed');
        return r.json();
      })
      .then(data => {
        setSchema(data.schema || []);
        setValues(data.settings || {});
      })
      .catch(() => setError(true));
  }, [id]);

  function updateField(key, val) {
    setValues(v => ({ ...v, [key]: val }));
  }

  function handleSubmit(e) {
    e.preventDefault();
    fetch(`${restRoot}artpulse/v1/widget-settings/${id}`, {
      method: 'POST',
      headers: { 'X-WP-Nonce': restNonce, 'Content-Type': 'application/json' },
      body: JSON.stringify({ settings: values })
    })
      .then(() => {
        if (window.wp?.data?.dispatch) {
          wp.data.dispatch('core/notices').createNotice('success', l10n.saveSuccess || 'Saved', { isDismissible: true });
        }
        onClose();
      })
      .catch(() => {
        if (window.wp?.data?.dispatch) {
          wp.data.dispatch('core/notices').createNotice('error', l10n.saveError || 'Error', { isDismissible: true });
        }
      });
  }

  return (
    <div className="ap-org-modal open" id="ap-widget-settings-modal">
      <div id="ap-widget-settings-content">
        <button type="button" className="ap-form-button" onClick={onClose}>{l10n.close || 'Close'}</button>
        {error && <p>Unable to load widget settings.</p>}
        <form onSubmit={handleSubmit}>
          {schema.map(field => {
            if (!field.key) return null;
            const val = values[field.key] ?? (field.type === 'checkbox' ? false : '');
            if (field.type === 'checkbox') {
              return (
                <label key={field.key} className="ap-form-label">
                  <input
                    type="checkbox"
                    checked={!!val}
                    onChange={e => updateField(field.key, e.target.checked)}
                  />
                  {field.label || field.key}
                </label>
              );
            }
            return (
              <label key={field.key} className="ap-form-label">
                {field.label || field.key}
                <input
                  type={field.type || 'text'}
                  value={val}
                  onChange={e => updateField(field.key, e.target.value)}
                />
              </label>
            );
          })}
          <button type="submit" className="ap-form-button">{l10n.save || 'Save'}</button>
        </form>
      </div>
    </div>
  );
}

function WidgetsEditor({ widgets, config, roles, nonce, ajaxUrl, l10n = {} }) {
  const roleKeys = Object.keys(roles);
  const [activeRole, setActiveRole] = useState(roleKeys[0] || '');
  const [active, setActive] = useState([]);
  const [available, setAvailable] = useState([]);
  const [showPreview, setShowPreview] = useState(false);
  const [selectedWidget, setSelectedWidget] = useState(null);
  const [defaults] = useState(() => JSON.parse(JSON.stringify(config)));
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

  function moveItem(list, from, to) {
    const copy = [...list];
    const item = copy.splice(from, 1)[0];
    copy.splice(to, 0, item);
    return copy;
  }

  function handleKeyDown(e, index, listName) {
    const isActive = listName === 'active';
    const list = isActive ? active : available;
    if (e.key === 'ArrowUp' && index > 0) {
      const newList = moveItem(list, index, index - 1);
      isActive ? setActive(newList) : setAvailable(newList);
      e.preventDefault();
    } else if (e.key === 'ArrowDown' && index < list.length - 1) {
      const newList = moveItem(list, index, index + 1);
      isActive ? setActive(newList) : setAvailable(newList);
      e.preventDefault();
    } else if (e.key === 'ArrowLeft' && isActive) {
      const item = list[index];
      const newAct = [...active];
      newAct.splice(index, 1);
      setActive(newAct);
      setAvailable([item, ...available]);
      e.preventDefault();
    } else if (e.key === 'ArrowRight' && !isActive) {
      const item = list[index];
      const newAvail = [...available];
      newAvail.splice(index, 1);
      setAvailable(newAvail);
      setActive([...active, item]);
      e.preventDefault();
    }
  }

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
      .then(res => {
        if (res.success) {
          if (window.wp?.data?.dispatch) {
            wp.data.dispatch('core/notices').createNotice('success', l10n.saveSuccess || 'Saved', { isDismissible: true });
          }
          config[activeRole] = active.map(w => w.id);
        } else {
          const msg = res.data?.message || l10n.saveError || 'Error';
          if (window.wp?.data?.dispatch) {
            wp.data.dispatch('core/notices').createNotice('error', msg, { isDismissible: true });
          }
        }
      })
      .catch(() => {
        if (window.wp?.data?.dispatch) {
          wp.data.dispatch('core/notices').createNotice('error', l10n.saveError || 'Error', { isDismissible: true });
        }
      });
  }

  function handleReset() {
    const activeIds = defaults[activeRole] || [];
    setActive(widgets.filter(w => activeIds.includes(w.id)));
    setAvailable(widgets.filter(w => !activeIds.includes(w.id)));
  }

  return (
    <div className="ap-widgets-editor">
      <label className="screen-reader-text" htmlFor="ap-role-select">{l10n.selectRole || 'Select Role'}</label>
      <select
        id="ap-role-select"
        value={activeRole}
        onChange={e => setActiveRole(e.target.value)}
      >
        {roleKeys.map(r => (
          <option key={r} value={r}>{roles[r].name || r}</option>
        ))}
      </select>
      <p className="ap-widgets-help">{l10n.instructions}</p>
      <div className="ap-widgets-columns">
        <div className="ap-widgets-available">
          <h4 id="ap-available-label">{l10n.availableWidgets || 'Available Widgets'}</h4>
          <ul ref={availRef} role="listbox" aria-labelledby="ap-available-label">
            {available.map((w, i) => (
              <li
                key={w.id}
                data-id={w.id}
                tabIndex={0}
                role="option"
                onClick={() => setSelectedWidget(w.id)}
                onKeyDown={e => handleKeyDown(e, i, 'available')}
              >
                {w.name}
              </li>
            ))}
          </ul>
        </div>
        <div className="ap-widgets-active">
          <h4 id="ap-active-label">{l10n.activeWidgets || 'Active Widgets'}</h4>
          <ul ref={activeRef} role="listbox" aria-labelledby="ap-active-label">
            {active.map((w, i) => (
              <li
                key={w.id}
                data-id={w.id}
                tabIndex={0}
                role="option"
                onClick={() => setSelectedWidget(w.id)}
                onKeyDown={e => handleKeyDown(e, i, 'active')}
              >
                {w.name}
              </li>
            ))}
          </ul>
        </div>
      </div>
      <div className="ap-widgets-actions">
        <button className="ap-form-button" onClick={handleSave}>{l10n.save || 'Save'}</button>
        <button className="ap-form-button" onClick={() => setShowPreview(!showPreview)}>{l10n.preview || 'Preview'}</button>
        <button className="ap-form-button" onClick={handleReset}>{l10n.resetDefault || 'Reset to Default'}</button>
      </div>
      {showPreview && (
        <div className="ap-widgets-preview">
          <h4>{l10n.preview || 'Preview'}</h4>
          <ol>
            {active.map(w => (
              <li key={w.id}>{w.name}</li>
            ))}
          </ol>
        </div>
      )}
      {selectedWidget && (
        <WidgetSettingsForm
          id={selectedWidget}
          onClose={() => setSelectedWidget(null)}
          l10n={l10n}
        />
      )}
    </div>
  );
}

document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('ap-dashboard-widgets-canvas');
  if (container && window.APDashboardWidgetsEditor) {
    createRoot(container).render(
      <WidgetsEditor {...APDashboardWidgetsEditor} />
    );
    console.log('Editor loaded');
  } else {
    console.error('WidgetsEditor failed: container or data missing');
  }
});
