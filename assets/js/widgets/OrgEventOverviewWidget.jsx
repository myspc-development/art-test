import React, { useEffect, useState } from 'react';
import { createRoot } from 'react-dom/client';
const { __ } = wp.i18n;

export function OrgEventOverviewWidget({ apiRoot, nonce, orgId }) {
  const [events, setEvents] = useState([]);

  useEffect(() => {
    fetch(`${apiRoot}artpulse/v1/org/${orgId}/events/summary`, {
      headers: { 'X-WP-Nonce': nonce },
      credentials: 'same-origin'
    })
      .then(r => r.json())
      .then(data => setEvents(data.events || []));
  }, [orgId]);

  return (
    <div className="ap-org-event-overview-widget">
      <ul>
        {events.map(ev => (
          <li key={ev.id}>{ev.title} ({ev.status})</li>
        ))}
      </ul>
    </div>
  );
}

export default function initOrgEventOverviewWidget(el) {
  const root = createRoot(el);
  const { apiRoot, nonce, orgId } = el.dataset;
  root.render(
    <OrgEventOverviewWidget apiRoot={apiRoot} nonce={nonce} orgId={orgId} />
  );
}
