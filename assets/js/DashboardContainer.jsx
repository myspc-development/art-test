import React, { useEffect, useState } from 'react';
import { WidthProvider, Responsive } from 'react-grid-layout';
const GridLayout = WidthProvider(Responsive);
import registry from './widgets/index.js';

export default function DashboardContainer({ role = 'member' }) {
  const apiRoot = window.ArtPulseDashboardApi?.apiUrl || window.ArtPulseDashboardApi?.root || '/wp-json/';
  const nonce = window.apNonce || window.ArtPulseDashboardApi?.nonce || '';
  const token = window.ArtPulseDashboardApi?.apiToken || '';
  const [layout, setLayout] = useState([]);
  const widgets = registry.filter(w => !w.roles || w.roles.includes(role));
  const widgetTitles = Object.fromEntries(widgets.map(w => [w.id, w.title]));

  useEffect(() => {
    const headers = { 'X-WP-Nonce': nonce };
    if (token) headers['Authorization'] = `Bearer ${token}`;
    fetch(`${apiRoot}artpulse/v1/ap_dashboard_layout`, {
      headers,
      credentials: 'same-origin'
    })
      .then(r => (r.status === 401 || r.status === 403 || r.status === 404 ? Promise.reject() : r.json()))
      .then(data => {
        const ids = Array.isArray(data.layout) ? data.layout : [];
        setLayout(
          ids.map((id, i) => ({ i: id, x: 0, y: i, w: 4, h: 2 }))
        );
      })
      .catch(() => setLayout([]));
  }, [role]);

  const handleLayoutChange = l => {
    setLayout(l);
    const ids = l.map(it => it.i);
    const headers = { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce };
    if (token) headers['Authorization'] = `Bearer ${token}`;
    fetch(`${apiRoot}artpulse/v1/ap_dashboard_layout`, {
      method: 'POST',
      headers,
      credentials: 'same-origin',
      body: JSON.stringify({ layout: ids })
    });
  };

  const widgetMap = Object.fromEntries(widgets.map(w => [w.id, w.component]));

  const handleKeyDown = (e, item) => {
    const key = e.key;
    let changes = null;
    if (e.shiftKey) {
      switch (key) {
        case 'ArrowLeft':
          changes = { w: Math.max(1, item.w - 1) };
          break;
        case 'ArrowRight':
          changes = { w: item.w + 1 };
          break;
        case 'ArrowUp':
          changes = { h: Math.max(1, item.h - 1) };
          break;
        case 'ArrowDown':
          changes = { h: item.h + 1 };
          break;
      }
    } else {
      switch (key) {
        case 'ArrowLeft':
          changes = { x: Math.max(0, item.x - 1) };
          break;
        case 'ArrowRight':
          changes = { x: item.x + 1 };
          break;
        case 'ArrowUp':
          changes = { y: Math.max(0, item.y - 1) };
          break;
        case 'ArrowDown':
          changes = { y: item.y + 1 };
          break;
      }
    }
    if (changes) {
      e.preventDefault();
      const updated = layout.map(it =>
        it.i === item.i ? { ...it, ...changes } : it
      );
      handleLayoutChange(updated);
    }
  };

  const breakpoints = { lg: 1200, md: 996, sm: 768, xs: 480, xxs: 0 };
  const cols = { lg: 12, md: 10, sm: 6, xs: 4, xxs: 2 };

  return (
    <GridLayout
      className="layout"
      role="grid"
      aria-label="Dashboard widgets"
      breakpoints={breakpoints}
      cols={cols}
      layouts={{ lg: layout, md: layout, sm: layout, xs: layout, xxs: layout }}
      rowHeight={30}
      onLayoutChange={l => handleLayoutChange(l)}
    >
      {layout.map(item => {
        const Comp = widgetMap[item.i];
        return (
          <div
            key={item.i}
            data-grid={item}
            role="gridcell"
            tabIndex={0}
            aria-label={widgetTitles[item.i]}
            onKeyDown={e => handleKeyDown(e, item)}
          >
            {Comp ? <Comp /> : <div className="ap-widget placeholder" role="region" aria-label="Unavailable Widget" />}
          </div>
        );
      })}
    </GridLayout>
  );
}

