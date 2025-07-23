import React, { useEffect, useState } from 'react';
import { WidthProvider, Responsive } from 'react-grid-layout';
const GridLayout = WidthProvider(Responsive);
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
      className="layout"
      layouts={{ lg: layout }}
      cols={{ lg: 12 }}
      rowHeight={30}
      onLayoutChange={(l, allLayouts) => handleLayoutChange(allLayouts.lg)}
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

