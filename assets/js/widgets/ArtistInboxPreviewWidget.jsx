import React, { useEffect, useState } from 'react';
import { createRoot } from 'react-dom/client';
const { __ } = wp.i18n;

export function ArtistInboxPreviewWidget({ apiRoot, nonce }) {
  const [threads, setThreads] = useState(null);

  useEffect(() => {
    fetch(`${apiRoot}artpulse/v1/conversations`, {
      headers: { 'X-WP-Nonce': nonce },
      credentials: 'same-origin'
    })
      .then(r => r.json())
      .then(async list => {
        const withPreview = await Promise.all(
          list.map(async t => {
            const res = await fetch(`${apiRoot}artpulse/v1/messages?with=${t.user_id}`, {
              headers: { 'X-WP-Nonce': nonce },
              credentials: 'same-origin'
            });
            const msgs = await res.json();
            const last = msgs[msgs.length - 1] || {};
            return { ...t, preview: last.content || '', date: last.created_at };
          })
        );
        setThreads(withPreview);
      })
      .catch(() => setThreads([]));
  }, []);

  if (threads === null) {
    return <p>{__('Loading...', 'artpulse')}</p>;
  }

  if (!threads.length) {
    return <p>{__('No new messages.', 'artpulse')}</p>;
  }

  return (
    <ul className="ap-inbox-preview-list">
      {threads.slice(0, 3).map(t => (
        <li key={t.user_id}>
          <strong>{t.display_name}</strong>
          {t.preview && <span>: {t.preview}</span>}
          {t.date && <em> {new Date(t.date).toLocaleDateString()}</em>}
        </li>
      ))}
    </ul>
  );
}

export default function initArtistInboxPreviewWidget(el) {
  const root = createRoot(el);
  const { apiRoot, nonce } = el.dataset;
  root.render(<ArtistInboxPreviewWidget apiRoot={apiRoot} nonce={nonce} />);
}
