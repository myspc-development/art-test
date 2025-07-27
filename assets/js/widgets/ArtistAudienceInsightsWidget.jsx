import React from 'react';
import { createRoot } from 'react-dom/client';
const { __ } = wp.i18n;

export function ArtistAudienceInsightsWidget() {
  return (
    <div className="ap-audience-insights-placeholder">
      {__('Audience insights coming soon.', 'artpulse')}
    </div>
  );
}

export default function initArtistAudienceInsightsWidget(el) {
  const root = createRoot(el);
  root.render(<ArtistAudienceInsightsWidget />);
}
