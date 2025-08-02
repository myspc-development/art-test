import React, { useEffect, useState } from 'react';
import { createRoot } from 'react-dom/client';
const { __ } = wp.i18n;

export function ArtistArtworkManagerWidget({ apiRoot, nonce }) {
  const [items, setItems] = useState(null);

  useEffect(() => {
    fetch(`${apiRoot}wp/v2/artpulse_artwork?per_page=5&_embed`, {
      headers: { 'X-WP-Nonce': nonce },
      credentials: 'same-origin'
    })
      .then(r => r.json())
      .then(data => setItems(Array.isArray(data) ? data : []))
      .catch(() => setItems([]));
  }, []);

  let content;
  if (items === null) {
    content = <p>{__('Loading...', 'artpulse')}</p>;
  } else if (!items.length) {
    content = <p>{__('No artworks found.', 'artpulse')}</p>;
  } else {
    content = (
      <ul className="ap-artwork-manager">
        {items.map(a => (
          <li key={a.id}>
            <a href={a.link}>{a.title?.rendered || a.slug}</a>
          </li>
        ))}
      </ul>
    );
  }

  return <div data-widget-id="artist_artwork_manager">{content}</div>;
}

export default function initArtistArtworkManagerWidget(el) {
  const root = createRoot(el);
  const { apiRoot = wpApiSettings?.root || '/wp-json/', nonce = wpApiSettings?.nonce || '' } = el.dataset;
  root.render(<ArtistArtworkManagerWidget apiRoot={apiRoot} nonce={nonce} />);
}
