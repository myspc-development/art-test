import React, { useEffect, useState, useMemo } from 'react';
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

const LOCAL_STORAGE_KEY = 'ap_preview_role';

const addHeadingId = (id: string, html: string) => {
  const parser = new DOMParser();
  const doc = parser.parseFromString(html, 'text/html');
  const heading = doc.querySelector('h1, h2, h3, h4, h5, h6');
  if (heading && !heading.id) {
    heading.id = `${id}-heading`;
  }
  return doc.body.innerHTML;
};

const DashboardCard: React.FC<{ id: string; visible?: boolean; children?: React.ReactNode }> = ({ id, visible = true, children }) => (
  <div
    className="ap-widget-card"
    data-id={id}
    data-visible={visible ? '1' : '0'}
    role="region"
    aria-labelledby={`${id}-heading`}
  >
    <span className="drag-handle" role="button" tabIndex={0} aria-label="Move widget"></span>
    {children}
  </div>
);

const RoleDashboard: React.FC = () => {
  const { RoleDashboardData = { widgets: [], currentUser: {} } } = window as WindowWithDashboard;

  const [previewRole, setPreviewRole] = useState<string | null>(
    () => window.localStorage.getItem(LOCAL_STORAGE_KEY)
  );
  const [availableRoles, setAvailableRoles] = useState<string[]>([]);

  const restRoot = (window as WindowWithDashboard).wpApiSettings?.root || '/wp-json/';
  const nonce = (window as WindowWithDashboard).wpApiSettings?.nonce || '';

  const [layout, setLayout] = useState<string[]>([]);
  const [visibility, setVisibility] = useState<Record<string, boolean>>({});

  useEffect(() => {
    fetch(restRoot + 'artpulse/v1/dashboard-config', {
      headers: { 'X-WP-Nonce': nonce },
    })
      .then(r => r.json())
      .then(data => {
        const roles = Object.keys(data.role_widgets || {});
        setAvailableRoles(roles);
      })
      .catch(err => {
        console.error('Failed to load roles', err);
      });
  }, [restRoot, nonce]);

  useEffect(() => {
    if (previewRole) {
      window.localStorage.setItem(LOCAL_STORAGE_KEY, previewRole);
    } else {
      window.localStorage.removeItem(LOCAL_STORAGE_KEY);
    }
  }, [previewRole]);

  const { widgets: allowed, error: configError, retry: retryConfig } = useFilteredWidgets(
    RoleDashboardData.widgets,
    { roles: previewRole ? [previewRole] : RoleDashboardData.currentUser.roles }
  );
  useEffect(() => {
    const role = previewRole || RoleDashboardData.currentUser.role;
    const url = `${restRoot}artpulse/v1/ap/layout${role ? `?role=${role}` : ''}`;
    fetch(url, {
      headers: { 'X-WP-Nonce': nonce },
    })
      .then(r => r.json())
      .then(data => {
        setLayout(Array.isArray(data.layout) ? data.layout : []);
        setVisibility(data.visibility || {});
      })
      .catch(err => {
        console.error('Failed to load layout', err);
        setLayout([]);
        setVisibility({});
      });
  }, [previewRole, restRoot, nonce, RoleDashboardData.currentUser.role]);

  const ordered = useMemo(() => {
    if (layout.length === 0) return allowed;
    const map = new Map(allowed.map(w => [w.id, w]));
    const sorted: WidgetDef[] = [];
    layout.forEach(id => {
      const w = map.get(id);
      if (w) sorted.push(w);
    });
    allowed.forEach(w => {
      if (!layout.includes(w.id)) sorted.push(w);
    });
    return sorted;
  }, [allowed, layout]);

  const visibleWidgets = useMemo(
    () => ordered.filter(w => visibility[w.id] !== false),
    [ordered, visibility]
  );

  useEffect(() => {
    if (process.env.NODE_ENV === 'development') {
      console.group('[RoleDashboard]');
      console.log('User role:', previewRole || RoleDashboardData.currentUser.role);
      console.log('Widgets rendered:', visibleWidgets.map(w => w.id));
      console.groupEnd();
    }
  }, [visibleWidgets, previewRole]);
  const [htmlMap, setHtmlMap] = useState<Record<string, string>>({});
  const [errorMap, setErrorMap] = useState<Record<string, boolean>>({});

  const fetchWidgetHtml = (id: string) => {
    fetch(`${restRoot}artpulse/v1/dashboard-widget/${id}`, {
      headers: { 'X-WP-Nonce': nonce },
    })
      .then(res => res.text())
      .then(html => {
        setHtmlMap(prev => ({ ...prev, [id]: addHeadingId(id, html) }));
        setErrorMap(prev => ({ ...prev, [id]: false }));
      })
      .catch(err => {
        console.error('Failed to load widget', id, err);
        setErrorMap(prev => ({ ...prev, [id]: true }));
      });
  };

  useEffect(() => {
    visibleWidgets.forEach(widget => {
      if (widget.restOnly && !htmlMap[widget.id] && !errorMap[widget.id]) {
        fetchWidgetHtml(widget.id);
      } else if (!widget.restOnly && widget.html && !htmlMap[widget.id]) {
        setHtmlMap(prev => ({ ...prev, [widget.id]: addHeadingId(widget.id, widget.html as string) }));
      }
    });
  }, [visibleWidgets, restRoot, nonce, htmlMap, errorMap]);

  return (
    <>
      <div className="ap-role-select">
        <label>
          Preview role:{' '}
          <select
            value={previewRole || ''}
            onChange={e => setPreviewRole(e.target.value || null)}
          >
            <option value="">(Your current role)</option>
            {availableRoles.map(role => (
              <option key={role} value={role}>
                {role}
              </option>
            ))}
          </select>
        </label>
      </div>
      {configError && (
        <div className="ap-error" role="alert">
          <p>{configError}</p>
          <button onClick={retryConfig}>Retry</button>
        </div>
      )}
      {visibleWidgets.length === 0 && <p>No widgets available for your role.</p>}
      {visibleWidgets.map(widget => (
        <DashboardCard
          key={widget.id}
          id={widget.id}
          visible={visibility[widget.id] !== false}
        >
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
