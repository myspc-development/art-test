import React, { useState } from 'react';
import { createRoot } from 'react-dom/client';
const { __ } = wp.i18n;

export function ArtistFeedPublisherWidget({ apiRoot, nonce }) {
  const [text, setText] = useState('');
  const [msg, setMsg] = useState('');

  const submit = async e => {
    e.preventDefault();
    setMsg('');
    const resp = await fetch(`${apiRoot}wp/v2/posts`, {
      method: 'POST',
      headers: {
        'X-WP-Nonce': nonce,
        'Content-Type': 'application/json'
      },
      credentials: 'same-origin',
      body: JSON.stringify({ title: text, status: 'publish' })
    });
    if (resp.ok) {
      setText('');
      setMsg(__('Posted!', 'artpulse'));
    } else {
      setMsg(__('Error posting.', 'artpulse'));
    }
  };

  return (
    <div data-widget-id="artist_feed_publisher">
      <form className="ap-feed-publisher" onSubmit={submit}>
        <textarea
          value={text}
          onChange={e => setText(e.target.value)}
          placeholder={__('Share an update...', 'artpulse')}
        />
        <button type="submit">{__('Publish', 'artpulse')}</button>
        {msg && <p className="ap-post-status">{msg}</p>}
      </form>
    </div>
  );
}

export default function initArtistFeedPublisherWidget(el) {
  const root = createRoot(el);
  const { apiRoot = wpApiSettings?.root || '/wp-json/', nonce = wpApiSettings?.nonce || '' } = el.dataset;
  root.render(<ArtistFeedPublisherWidget apiRoot={apiRoot} nonce={nonce} />);
}
