import React, { useEffect, useState } from 'react';
import { createRoot } from 'react-dom/client';

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
    return <p>Loading...</p>;
  }

  if (!items.length) {
    return <p>No mentions yet.</p>;
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
