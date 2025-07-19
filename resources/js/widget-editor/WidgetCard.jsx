import React from 'react';

export default function WidgetCard({ id, name, visible, onToggle, onRemove }) {
  return (
    <div className="ap-widget-card" data-id={id}>
      <span className="widget-name">{name}</span>
      <button type="button" onClick={onToggle}>
        {visible ? 'Hide' : 'Show'}
      </button>
      <button type="button" onClick={onRemove}>
        Remove
      </button>
    </div>
  );
}
