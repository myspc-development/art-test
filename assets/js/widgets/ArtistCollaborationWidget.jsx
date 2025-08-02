import React, { useEffect, useState } from 'react';
import { createRoot } from 'react-dom/client';
const { __ } = wp.i18n;

export function ArtistCollaborationWidget({ apiRoot, nonce }) {
  const [invites, setInvites] = useState(null);

  useEffect(() => {
    fetch(`${apiRoot}artpulse/v1/users/me/orgs`, {
      headers: { 'X-WP-Nonce': nonce },
      credentials: 'same-origin'
    })
      .then(r => r.json())
      .then(async list => {
        const pending = list.filter(i => i.status && i.status !== 'active');
        const detailed = await Promise.all(
          pending.map(async inv => {
            try {
              const res = await fetch(`${apiRoot}wp/v2/artpulse_org/${inv.org_id}`);
              const org = await res.json();
              return { ...inv, name: org.title?.rendered || inv.org_id };
            } catch {
              return { ...inv, name: inv.org_id };
            }
          })
        );
        setInvites(detailed);
      })
      .catch(() => setInvites([]));
  }, []);

  let content;
  if (invites === null) {
    content = <p>{__('Loading...', 'artpulse')}</p>;
  } else if (!invites.length) {
    content = <p>{__('No pending invites.', 'artpulse')}</p>;
  } else {
    content = (
      <ul className="ap-collab-requests">
        {invites.map(inv => (
          <li key={inv.org_id}>
            {inv.name} – {inv.role} ({inv.status})
          </li>
        ))}
      </ul>
    );
  }

  return <div data-widget-id="collab_requests">{content}</div>;
}

export default function initArtistCollaborationWidget(el) {
  const root = createRoot(el);
  const { apiRoot = wpApiSettings?.root || '/wp-json/', nonce = wpApiSettings?.nonce || '' } = el.dataset;
  root.render(<ArtistCollaborationWidget apiRoot={apiRoot} nonce={nonce} />);
}
