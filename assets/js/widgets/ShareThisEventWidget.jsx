import React from 'react';
import { createRoot } from 'react-dom/client';
const { __ } = wp.i18n;

/**
 * Social share widget for events.
 *
 * Props:
 * - eventUrl: URL of the event page.
 */
export function ShareThisEventWidget({ eventUrl }) {
  const share = (prefix) => {
    if (navigator.share) {
      navigator.share({ url: eventUrl }).catch(() => {});
      return;
    }
    window.open(prefix + encodeURIComponent(eventUrl), '_blank');
  };

  const copy = () => {
    navigator.clipboard.writeText(eventUrl).then(() => {
      alert(__('Link copied', 'artpulse'));
    });
  };

  return (
    <div className="ap-share-event-widget" data-widget-id="share_this_event">
      <button onClick={() => share('https://twitter.com/share?url=')}>X</button>
      <button onClick={() => share('https://www.facebook.com/sharer/sharer.php?u=')}>{__('Facebook', 'artpulse')}</button>
      <button onClick={() => share('https://www.linkedin.com/sharing/share-offsite/?url=')}>{__('LinkedIn', 'artpulse')}</button>
      <button onClick={copy}>{__('Copy Link', 'artpulse')}</button>
    </div>
  );
}

export default function initShareThisEventWidget(el) {
  const root = createRoot(el);
  const { eventUrl } = el.dataset;
  root.render(<ShareThisEventWidget eventUrl={eventUrl} />);
}
