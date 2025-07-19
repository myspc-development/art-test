import React, { useEffect, useState } from 'react';
import GridLayout from 'react-grid-layout';
import registry from './widgets/index.js';

export default function DashboardContainer({ role = 'member' }) {
  const apiRoot = window.ArtPulseDashboardApi?.root || '/wp-json/';
  const nonce = window.ArtPulseDashboardApi?.nonce || '';
  const [layout, setLayout] = useState([]);
  const widgets = registry.filter(w => !w.roles || w.roles.includes(role));

  useEffect(() => {
    fetch(`${apiRoot}artpulse/v1/ap_dashboard_layout`)
      .then(r => r.json())
      .then(data => {
        const ids = Array.isArray(data.layout) ? data.layout : [];
        setLayout(
          ids.map((id, i) => ({ i: id, x: 0, y: i, w: 4, h: 2 }))
        );
      });
  }, [role]);

  const handleLayoutChange = l => {
    setLayout(l);
    const ids = l.map(it => it.i);
    fetch(`${apiRoot}artpulse/v1/ap_dashboard_layout`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
      body: JSON.stringify({ layout: ids })
    });
  };

  const widgetMap = Object.fromEntries(widgets.map(w => [w.id, w.component]));

  return (
    <GridLayout
      layout={layout}
      cols={12}
      rowHeight={30}
      width={800}
      onLayoutChange={handleLayoutChange}
    >
      {layout.map(item => {
        const Comp = widgetMap[item.i];
        return (
          <div key={item.i} data-grid={item}>
            {Comp ? <Comp /> : item.i}
          </div>
        );
      })}
    </GridLayout>
  );
}

