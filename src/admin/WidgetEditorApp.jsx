import React, { useState, useEffect } from 'react';
import WidgetPalette from './WidgetPalette';
import DashboardCanvas from './DashboardCanvas';
import PropertiesPanel from './PropertiesPanel';
import PreviewToggle from './PreviewToggle';
import PreviewWrapper from './PreviewWrapper';
import widgetsData from './mock/widgetData.json';
import 'react-grid-layout/css/styles.css';
import './styles/editor.css';

export default function WidgetEditorApp() {
  const [context, setContext] = useState('member');
  const [layout, setLayout] = useState([]);
  const [widgets, setWidgets] = useState([]);
  const [selected, setSelected] = useState(null);
  const [preview, setPreview] = useState(false);

  useEffect(() => {
    setWidgets(widgetsData);
  }, []);

  const addWidget = (def) => {
    setLayout(l => [
      ...l,
      {
        i: `${def.id}-${Date.now()}`,
        x: 0,
        y: Infinity,
        w: def.defaultSize.w,
        h: def.defaultSize.h,
        widgetId: def.id,
        props: {},
      },
    ]);
  };

  const handleLayoutChange = (l) => {
    setLayout(l.map(item => ({ ...item, widgetId: item.widgetId })));
  };

  const currentWidget = layout.find(w => w.i === selected);

  return (
    <div>
      <h3>Widget Editor</h3>
      <select value={context} onChange={e => setContext(e.target.value)}>
        <option value="member">Member</option>
        <option value="artist">Artist</option>
        <option value="organization">Organization</option>
      </select>
      <PreviewToggle preview={preview} onToggle={setPreview} />
      {preview ? (
        <PreviewWrapper>
          {layout.map(item => (
            <div key={item.i}>{item.widgetId}</div>
          ))}
        </PreviewWrapper>
      ) : (
        <div className="ap-widget-editor">
          <WidgetPalette widgets={widgets} onAdd={addWidget} />
          <DashboardCanvas layout={layout} onLayoutChange={handleLayoutChange}>
            {layout.map(item => (
              <div
                key={item.i}
                data-grid={item}
                onClick={() => setSelected(item.i)}
                style={{ border: selected === item.i ? '2px solid blue' : '1px solid #ccc', background: '#fff' }}
              >
                {item.widgetId}
              </div>
            ))}
          </DashboardCanvas>
          <PropertiesPanel
            widget={widgets.find(w => w.id === currentWidget?.widgetId)}
            values={currentWidget?.props || {}}
            onChange={vals => {
              setLayout(l => l.map(it => it.i === currentWidget.i ? { ...it, props: vals } : it));
            }}
          />
        </div>
      )}
    </div>
  );
}
