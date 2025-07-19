import React from 'react';

export default function PropertiesPanel({ widget, values = {}, onChange }) {
  if (!widget) return <div className="ap-properties-panel">Select a widget</div>;

  const handle = (key, val) => {
    onChange({ ...values, [key]: val });
  };

  return (
    <div className="ap-properties-panel">
      <h4>{widget.label} Settings</h4>
      {widget.props?.map(f => (
        <label key={f.key}>
          {f.label}
          <input
            type="text"
            value={values[f.key] || ''}
            onChange={e => handle(f.key, e.target.value)}
          />
        </label>
      ))}
    </div>
  );
}
