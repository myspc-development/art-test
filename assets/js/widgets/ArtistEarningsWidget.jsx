import React, { useEffect, useState } from 'react';
import { createRoot } from 'react-dom/client';
const { __ } = wp.i18n;

export function ArtistEarningsWidget({ apiRoot, nonce }) {
  const [data, setData] = useState(null);

  useEffect(() => {
    fetch(`${apiRoot}artpulse/v1/user/payouts`, {
      headers: { 'X-WP-Nonce': nonce },
      credentials: 'same-origin'
    })
      .then(r => r.json())
      .then(setData)
      .catch(() => setData({ payouts: [], balance: 0 }));
  }, []);

  if (!data) {
    return (
      <div data-widget-id="artist_earnings_summary">
        <p>{__('Loading...', 'artpulse')}</p>
      </div>
    );
  }

  return (
    <div className="ap-earnings-summary" data-widget-id="artist_earnings_summary">
      <p><strong>{data.balance}</strong> {__('current balance', 'artpulse')}</p>
      {data.payouts.length ? (
        <ul>
          {data.payouts.slice(0, 5).map(p => (
            <li key={p.id}>
              {new Date(p.payout_date).toLocaleDateString()} – {p.amount} ({p.status})
            </li>
          ))}
        </ul>
      ) : (
        <p>{__('No payouts yet.', 'artpulse')}</p>
      )}
    </div>
  );
}

export default function initArtistEarningsWidget(el) {
  const root = createRoot(el);
  const { apiRoot = wpApiSettings?.root || '/wp-json/', nonce = wpApiSettings?.nonce || '' } = el.dataset;
  root.render(<ArtistEarningsWidget apiRoot={apiRoot} nonce={nonce} />);
}
