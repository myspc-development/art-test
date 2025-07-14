import React from 'react';

export default function WidgetSettingsForm({ schema = [], values = {}, onChange }) {
  if (!schema || schema.length === 0) return null;

  const handleChange = (key, type, e) => {
    let value;
    if (type === 'checkbox') {
      value = e.target.checked;
    } else if (type === 'number') {
      value = parseFloat(e.target.value) || 0;
    } else {
      value = e.target.value;
    }
    onChange(key, value);
  };

  return (
    <div className="ap-widget-settings-form">
      {schema.map(field => {
        const { key, label, type } = field;
        const value = values[key] ?? field.default ?? '';

        return (
          <div key={key} className="ap-setting-field" style={{ marginBottom: '1rem' }}>
            <label style={{ display: 'block', marginBottom: '0.25rem' }}>
              {label || key}
            </label>

            {type === 'checkbox' ? (
              <input
                type="checkbox"
                checked={!!value}
                onChange={e => handleChange(key, type, e)}
              />
            ) : (
              <input
                type={type || 'text'}
                value={value}
                onChange={e => handleChange(key, type, e)}
              />
            )}
          </div>
        );
      })}
    </div>
  );
}
