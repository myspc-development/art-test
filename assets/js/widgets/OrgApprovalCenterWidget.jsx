import React, { useEffect, useState } from 'react';
import { createRoot } from 'react-dom/client';
const { __ } = wp.i18n;

export function OrgApprovalCenterWidget({ apiRoot, nonce, orgId }) {
  const [items, setItems] = useState([]);

  useEffect(() => {
    fetch(`${apiRoot}artpulse/v1/org/${orgId}/submissions`, {
      headers: { 'X-WP-Nonce': nonce },
      credentials: 'same-origin'
    })
      .then(r => r.json())
      .then(data => setItems(Array.isArray(data) ? data : []));
  }, [orgId]);

  return (
    <div className="ap-org-approval-center-widget" data-widget-id="org_approval_center">
      <ul>
        {items.map(item => (
          <li key={item.id}>{item.title} - {item.status}</li>
        ))}
      </ul>
    </div>
  );
}

export default function initOrgApprovalCenterWidget(el) {
  const root = createRoot(el);
  const { apiRoot, nonce, orgId } = el.dataset;
  root.render(
    <OrgApprovalCenterWidget apiRoot={apiRoot} nonce={nonce} orgId={orgId} />
  );
}
