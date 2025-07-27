import React from 'react';
import { createRoot } from 'react-dom/client';
const { __ } = wp.i18n;

export function ArtistCollaborationWidget() {
  return (
    <div className="ap-collab-requests-placeholder">
      {__('Collaboration requests coming soon.', 'artpulse')}
    </div>
  );
}

export default function initArtistCollaborationWidget(el) {
  const root = createRoot(el);
  root.render(<ArtistCollaborationWidget />);
}
