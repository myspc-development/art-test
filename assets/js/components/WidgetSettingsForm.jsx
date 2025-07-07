import React from 'react';

export default function WidgetSettingsForm({ schema, values, onChange }) {
  return (
    <form className="widget-settings-form">
      {schema.map(({ key, label, type = 'text' }) => {
        const value = values[key];

        if (type === 'checkbox') {
          return (
            <label key={key}>
              <input
                type="checkbox"
                checked={!!value}
                onChange={e => onChange(key, e.target.checked)}
              />
              {label}
            </label>
          );
        }

        if (type === 'number') {
          return (
            <label key={key}>
              {label}
              <input
                type="number"
                value={value}
                onChange={e => onChange(key, parseFloat(e.target.value))}
              />
            </label>
          );
        }

        return (
          <label key={key}>
            {label}
            <input
              type="text"
              value={value}
              onChange={e => onChange(key, e.target.value)}
            />
          </label>
        );
      })}
    </form>
  );
}
