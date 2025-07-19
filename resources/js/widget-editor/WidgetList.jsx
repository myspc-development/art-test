import React from 'react';
import WidgetCard from './WidgetCard';

export default function WidgetList({ widgets, layout, setLayout }) {
  const toggleWidget = id => {
    setLayout(l =>
      l.map(w => (w.id === id ? { ...w, visible: !w.visible } : w))
    );
  };
  const removeWidget = id => {
    setLayout(l => l.filter(w => w.id !== id));
  };
  return (
    <div>
      {layout.map(item => {
        const widget = widgets.find(w => w.id === item.id) || {};
        return (
          <WidgetCard
            key={item.id}
            id={item.id}
            name={widget.name || item.id}
            visible={item.visible}
            onToggle={() => toggleWidget(item.id)}
            onRemove={() => removeWidget(item.id)}
          />
        );
      })}
    </div>
  );
}
