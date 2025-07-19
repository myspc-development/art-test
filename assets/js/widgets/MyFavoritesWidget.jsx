import React, { useEffect, useState } from 'react';
import { createRoot } from 'react-dom/client';

export function MyFavoritesWidget({ apiRoot, nonce }) {
  const [items, setItems] = useState([]);

  useEffect(() => {
    fetch(`${apiRoot}artpulse/v1/follows?post_type=artpulse_event`, {
      headers: { 'X-WP-Nonce': nonce },
      credentials: 'same-origin'
    })
      .then(r => r.json())
      .then(setItems);
  }, []);

  return (
    <div className="ap-favorites-widget">
      <ul>
        {items.map(i => (
          <li key={i.post_id}><a href={i.link}>{i.title}</a></li>
        ))}
      </ul>
    </div>
  );
}

export default function initMyFavoritesWidget(el) {
  const root = createRoot(el);
  const { apiRoot, nonce } = el.dataset;
  root.render(<MyFavoritesWidget apiRoot={apiRoot} nonce={nonce} />);
}
