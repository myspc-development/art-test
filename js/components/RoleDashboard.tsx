import React, { useEffect, useState } from 'react';
import useFilteredWidgets from '../../dashboard/useFilteredWidgets';

interface WidgetDef {
  id: string;
  roles?: string[];
  restOnly?: boolean;
  html?: string;
}

interface DashboardData {
  widgets: WidgetDef[];
  currentUser: {
    role?: string;
    roles?: string[];
  };
}

interface WindowWithDashboard extends Window {
  RoleDashboardData?: DashboardData;
  wpApiSettings?: { root: string; nonce: string };
}

const DashboardCard: React.FC<{ id: string; visible?: boolean; children?: React.ReactNode }> = ({ id, visible = true, children }) => (
  <div className="ap-widget-card" data-id={id} data-visible={visible ? '1' : '0'}>
    <span className="drag-handle" role="button" tabIndex={0} aria-label="Move widget"></span>
    {children}
  </div>
);

const RoleDashboard: React.FC = () => {
  const { RoleDashboardData = { widgets: [], currentUser: {} } } = window as WindowWithDashboard;
  const allowed = useFilteredWidgets(RoleDashboardData.widgets, RoleDashboardData.currentUser);
  const [htmlMap, setHtmlMap] = useState<Record<string, string>>({});

  const restRoot = (window as WindowWithDashboard).wpApiSettings?.root || '/wp-json/';
  const nonce = (window as WindowWithDashboard).wpApiSettings?.nonce || '';

  useEffect(() => {
    allowed.forEach(widget => {
      if (widget.restOnly && !htmlMap[widget.id]) {
        fetch(`${restRoot}artpulse/v1/dashboard-widget/${widget.id}`, {
          headers: { 'X-WP-Nonce': nonce },
        })
          .then(res => res.text())
          .then(html => setHtmlMap(prev => ({ ...prev, [widget.id]: html })))
          .catch(() => {});
      } else if (!widget.restOnly && widget.html && !htmlMap[widget.id]) {
        setHtmlMap(prev => ({ ...prev, [widget.id]: widget.html as string }));
      }
    });
  }, [allowed, restRoot, nonce, htmlMap]);

  return (
    <>
      {allowed.map(widget => (
        <DashboardCard key={widget.id} id={widget.id}>
          <div dangerouslySetInnerHTML={{ __html: htmlMap[widget.id] || '' }} />
        </DashboardCard>
      ))}
    </>
  );
};

export default RoleDashboard;
