import React from 'react';

export default function WidgetPalette({ widgets = [], onAdd }) {
  return (
    <div className="ap-widget-palette">
      <h4>Widgets</h4>
      <ul>
        {widgets.map(w => (
          <li key={w.id}>
            <button type="button" onClick={() => onAdd(w)}>
              {w.label}
            </button>
          </li>
        ))}
      </ul>
    </div>
  );
}
