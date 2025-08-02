import React, { useEffect, useState } from 'react';
import { createRoot } from 'react-dom/client';
const { __ } = wp.i18n;

export function OrgTicketInsightsWidget({ apiRoot, nonce, orgId }) {
  const [metrics, setMetrics] = useState({ sales: 0, revenue: 0 });

  useEffect(() => {
    fetch(`${apiRoot}artpulse/v1/org/${orgId}/tickets/metrics`, {
      headers: { 'X-WP-Nonce': nonce },
      credentials: 'same-origin'
    })
      .then(r => r.json())
      .then(data => setMetrics(data));
  }, [orgId]);

  return (
    <div className="ap-org-ticket-insights-widget" data-widget-id="org_ticket_insights">
      <p>{__('Tickets Sold', 'artpulse')}: {metrics.sales}</p>
      <p>{__('Revenue', 'artpulse')}: {metrics.revenue}</p>
    </div>
  );
}

export default function initOrgTicketInsightsWidget(el) {
  const root = createRoot(el);
  const { apiRoot, nonce, orgId } = el.dataset;
  root.render(
    <OrgTicketInsightsWidget apiRoot={apiRoot} nonce={nonce} orgId={orgId} />
  );
}
