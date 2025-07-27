import React from 'react';
import { createRoot } from 'react-dom/client';
const { __ } = wp.i18n;

export function ArtistEarningsWidget() {
  return (
    <div className="ap-earnings-summary-placeholder">
      {__('Earnings summary coming soon.', 'artpulse')}
    </div>
  );
}

export default function initArtistEarningsWidget(el) {
  const root = createRoot(el);
  root.render(<ArtistEarningsWidget />);
}
