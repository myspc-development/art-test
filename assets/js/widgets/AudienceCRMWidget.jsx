import React, { useEffect, useState } from 'react';
import { createRoot } from 'react-dom/client';

export function AudienceCRMWidget({ apiRoot, nonce, orgId }) {
  const [contacts, setContacts] = useState([]);

  useEffect(() => {
    fetch(`${apiRoot}artpulse/v1/org/${orgId}/audience`, {
      headers: { 'X-WP-Nonce': nonce },
      credentials: 'same-origin'
    })
      .then(r => r.json())
      .then(setContacts);
  }, [orgId]);

  return (
    <div className="ap-audience-crm-widget">
      <ul>
        {contacts.map(c => (
          <li key={c.email}>{c.name || c.email}</li>
        ))}
      </ul>
    </div>
  );
}

export default function initAudienceCRMWidget(el) {
  const root = createRoot(el);
  const { apiRoot, nonce, orgId } = el.dataset;
  root.render(<AudienceCRMWidget apiRoot={apiRoot} nonce={nonce} orgId={orgId} />);
}
