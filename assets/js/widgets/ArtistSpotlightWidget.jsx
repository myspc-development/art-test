import React, { useEffect, useState } from 'react';
import { createRoot } from 'react-dom/client';
const { __ } = wp.i18n;

export function ArtistSpotlightWidget({ apiRoot, nonce }) {
  const [items, setItems] = useState(null);

  useEffect(() => {
    fetch(`${apiRoot}artpulse/v1/spotlights`, {
      headers: { 'X-WP-Nonce': nonce },
      credentials: 'same-origin'
    })
      .then(r => r.json())
      .then(setItems)
      .catch(() => setItems([]));
  }, []);

  if (items === null) {
    return <p>{__('Loading...', 'artpulse')}</p>;
  }

  if (!items.length) {
    return <p>{__('No mentions yet.', 'artpulse')}</p>;
  }

  return (
    <ul className="ap-spotlight-list">
      {items.slice(0, 3).map(it => (
        <li key={it.id}>
          <a href={it.link}>{it.title}</a>
        </li>
      ))}
    </ul>
  );
}

export default function initArtistSpotlightWidget(el) {
  const root = createRoot(el);
  const { apiRoot, nonce } = el.dataset;
  root.render(<ArtistSpotlightWidget apiRoot={apiRoot} nonce={nonce} />);
}
