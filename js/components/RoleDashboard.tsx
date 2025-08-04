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
  const [errorMap, setErrorMap] = useState<Record<string, boolean>>({});

  const restRoot = (window as WindowWithDashboard).wpApiSettings?.root || '/wp-json/';
  const nonce = (window as WindowWithDashboard).wpApiSettings?.nonce || '';

  const fetchWidgetHtml = (id: string) => {
    fetch(`${restRoot}artpulse/v1/dashboard-widget/${id}`, {
      headers: { 'X-WP-Nonce': nonce },
    })
      .then(res => res.text())
      .then(html => {
        setHtmlMap(prev => ({ ...prev, [id]: html }));
        setErrorMap(prev => ({ ...prev, [id]: false }));
      })
      .catch(err => {
        console.error('Failed to load widget', id, err);
        alert('Failed to load widget.');
        setErrorMap(prev => ({ ...prev, [id]: true }));
      });
  };

  useEffect(() => {
    allowed.forEach(widget => {
      if (widget.restOnly && !htmlMap[widget.id] && !errorMap[widget.id]) {
        fetchWidgetHtml(widget.id);
      } else if (!widget.restOnly && widget.html && !htmlMap[widget.id]) {
        setHtmlMap(prev => ({ ...prev, [widget.id]: widget.html as string }));
      }
    });
  }, [allowed, restRoot, nonce, htmlMap, errorMap]);

  return (
    <>
      {allowed.map(widget => (
        <DashboardCard key={widget.id} id={widget.id}>
          {errorMap[widget.id] ? (
            <div>
              <p>Failed to load widget.</p>
              <button onClick={() => fetchWidgetHtml(widget.id)}>Retry</button>
            </div>
          ) : (
            <div dangerouslySetInnerHTML={{ __html: htmlMap[widget.id] || '' }} />
          )}
        </DashboardCard>
      ))}
    </>
  );
};

export default RoleDashboard;
