import React, { useState } from 'react';
import { createRoot } from 'react-dom/client';

/**
 * Button widget for RSVP actions.
 *
 * Props:
 * - eventId: Event post ID.
 * - apiRoot: REST API root URL.
 * - nonce: WP nonce for authentication.
 */
export function RSVPButton({ eventId, apiRoot, nonce }) {
  const [rsvped, setRsvped] = useState(false);

  const toggle = async () => {
    const endpoint = rsvped ? 'rsvp/cancel' : 'rsvp';
    const url = `${apiRoot}artpulse/v1/${endpoint}`;
    const resp = await fetch(url, {
      method: 'POST',
      headers: {
        'X-WP-Nonce': nonce,
        'Content-Type': 'application/json'
      },
      credentials: 'same-origin',
      body: JSON.stringify({ event_id: eventId })
    });
    if (resp.ok) {
      setRsvped(!rsvped);
    }
  };

  return (
    <button className={`ap-rsvp-btn${rsvped ? ' is-rsvped' : ''}`} onClick={toggle}>
      {rsvped ? 'Cancel RSVP' : 'RSVP'}
    </button>
  );
}

export default function initRSVPButton(el) {
  const root = createRoot(el);
  const { eventId, apiRoot, nonce } = el.dataset;
  root.render(
    <RSVPButton eventId={Number(eventId)} apiRoot={apiRoot} nonce={nonce} />
  );
}
