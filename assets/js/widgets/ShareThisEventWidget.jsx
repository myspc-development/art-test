import React from 'react';
import { createRoot } from 'react-dom/client';

/**
 * Social share widget for events.
 *
 * Props:
 * - eventUrl: URL of the event page.
 */
export function ShareThisEventWidget({ eventUrl }) {
  // TODO: enhance share functionality per roadmap
  const share = (prefix) => {
    window.open(prefix + encodeURIComponent(eventUrl), '_blank');
  };

  return (
    <div className="ap-share-event-widget">
      <button onClick={() => share('https://twitter.com/share?url=')}>X</button>
      <button onClick={() => share('https://www.facebook.com/sharer/sharer.php?u=')}>Facebook</button>
      <button onClick={() => share('https://www.linkedin.com/sharing/share-offsite/?url=')}>LinkedIn</button>
      <button onClick={() => navigator.clipboard.writeText(eventUrl)}>Copy Link</button>
    </div>
  );
}

export default function initShareThisEventWidget(el) {
  const root = createRoot(el);
  const { eventUrl } = el.dataset;
  root.render(<ShareThisEventWidget eventUrl={eventUrl} />);
}
