import React from 'react';
import { createRoot } from 'react-dom/client';
const { __ } = wp.i18n;

export function ArtistFeedPublisherWidget() {
  return (
    <div className="ap-feed-publisher-placeholder">
      {__('Post composer coming soon.', 'artpulse')}
    </div>
  );
}

export default function initArtistFeedPublisherWidget(el) {
  const root = createRoot(el);
  root.render(<ArtistFeedPublisherWidget />);
}
