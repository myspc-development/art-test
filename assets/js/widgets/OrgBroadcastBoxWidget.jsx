import React, { useState } from 'react';
import { createRoot } from 'react-dom/client';
const { __ } = wp.i18n;

export function OrgBroadcastBoxWidget({ apiRoot, nonce, orgId }) {
  const [text, setText] = useState('');
  const send = async () => {
    await fetch(`${apiRoot}artpulse/v1/org/${orgId}/message/broadcast`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
      credentials: 'same-origin',
      body: JSON.stringify({ message: text })
    });
    setText('');
  };

  return (
    <div className="ap-org-broadcast-box-widget" data-widget-id="org_broadcast_box">
      <textarea value={text} onChange={e => setText(e.target.value)} />
      <button onClick={send}>{__('Send Broadcast', 'artpulse')}</button>
    </div>
  );
}

export default function initOrgBroadcastBoxWidget(el) {
  const root = createRoot(el);
  const { apiRoot, nonce, orgId } = el.dataset;
  root.render(
    <OrgBroadcastBoxWidget apiRoot={apiRoot} nonce={nonce} orgId={orgId} />
  );
}
