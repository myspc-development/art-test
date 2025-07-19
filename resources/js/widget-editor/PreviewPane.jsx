import React from 'react';

export default function PreviewPane({ layout = [], widgets = [] }) {
  return (
    <div className="ap-preview-pane">
      <h3>Preview</h3>
      <ol>
        {layout
          .filter(w => w.visible !== false)
          .map(item => {
            const w = widgets.find(wd => wd.id === item.id) || {};
            return <li key={item.id}>{w.name || item.id}</li>;
          })}
      </ol>
    </div>
  );
}
