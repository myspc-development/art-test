import React, { useEffect, useState } from 'react';
import { createRoot } from 'react-dom/client';
const { __ } = wp.i18n;

export function ArtistAudienceInsightsWidget({ apiRoot, nonce }) {
  const [stats, setStats] = useState(null);

  useEffect(() => {
    fetch(`${apiRoot}artpulse/v1/artist`, {
      headers: { 'X-WP-Nonce': nonce },
      credentials: 'same-origin'
    })
      .then(r => r.json())
      .then(setStats)
      .catch(() => setStats({ followers: 0, sales: 0, artworks: 0 }));
  }, []);

  if (!stats) {
    return <p>{__('Loading...', 'artpulse')}</p>;
  }

  return (
    <ul className="ap-audience-insights">
      <li><strong>{stats.followers}</strong> {__('followers', 'artpulse')}</li>
      <li><strong>{stats.sales}</strong> {__('total sales', 'artpulse')}</li>
      <li><strong>{stats.artworks}</strong> {__('artworks uploaded', 'artpulse')}</li>
    </ul>
  );
}

export default function initArtistAudienceInsightsWidget(el) {
  const root = createRoot(el);
  const { apiRoot = wpApiSettings?.root || '/wp-json/', nonce = wpApiSettings?.nonce || '' } = el.dataset;
  root.render(<ArtistAudienceInsightsWidget apiRoot={apiRoot} nonce={nonce} />);
}
