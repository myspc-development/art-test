import React from 'react';
import { createRoot } from 'react-dom/client';
const { __ } = wp.i18n;

export function ArtistArtworkManagerWidget() {
  return (
    <div className="ap-artwork-manager-placeholder">
      {__('Artwork manager coming soon.', 'artpulse')}
    </div>
  );
}

export default function initArtistArtworkManagerWidget(el) {
  const root = createRoot(el);
  root.render(<ArtistArtworkManagerWidget />);
}
