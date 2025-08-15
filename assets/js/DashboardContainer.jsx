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
    fetch(`${apiRoot}artpulse/v1/ap_dashboard_layout`, {
      headers: { 'X-WP-Nonce': nonce }
    })
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
      breakpoints={{ lg: 1200, md: 996, sm: 768, xs: 480, xxs: 0 }}
      cols={{ lg: 12, md: 10, sm: 6, xs: 4, xxs: 2 }}
      layouts={{ lg: layout, md: layout, sm: layout, xs: layout, xxs: layout }}
      rowHeight={30}
      onLayoutChange={l => handleLayoutChange(l)}
    >
      {layout.map(item => {
        const Comp = widgetMap[item.i];
        return (
          <div key={item.i} data-grid={item}>
            {Comp ? (
              <Comp />
            ) : (
              <div role="region" aria-label="Unavailable Widget">
                <p>ⓘ This widget is available via API only and will be activated soon.</p>
              </div>
            )}
          </div>
        );
      })}
    </GridLayout>
  );
}

