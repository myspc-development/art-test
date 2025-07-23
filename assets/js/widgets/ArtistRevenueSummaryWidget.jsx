import React, { useEffect, useState } from 'react';
import { createRoot } from 'react-dom/client';

export function ArtistRevenueSummaryWidget({ apiRoot, nonce }) {
  const [data, setData] = useState(null);

  useEffect(() => {
    const now = new Date();
    const start = new Date(now.getFullYear(), now.getMonth(), 1)
      .toISOString()
      .slice(0, 10);
    fetch(`${apiRoot}artpulse/v1/user/sales?from=${start}`, {
      headers: { 'X-WP-Nonce': nonce },
      credentials: 'same-origin'
    })
      .then(r => r.json())
      .then(sales => setData(sales))
      .catch(() => setData({ tickets_sold: 0, total_revenue: 0, trend: [] }));
  }, []);

  if (!data) {
    return <p>Loading...</p>;
  }

  return (
    <div className="ap-revenue-summary">
      <p><strong>{data.total_revenue}</strong> total revenue this month</p>
      <p>{data.tickets_sold} tickets sold</p>
    </div>
  );
}

export default function initArtistRevenueSummaryWidget(el) {
  const root = createRoot(el);
  const { apiRoot, nonce } = el.dataset;
  root.render(<ArtistRevenueSummaryWidget apiRoot={apiRoot} nonce={nonce} />);
}
