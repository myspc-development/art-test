import React, { useState, useEffect } from 'react';
import { createRoot } from 'react-dom/client';
const { __ } = wp.i18n;

export function SponsoredEventConfigWidget({ postId, apiRoot, nonce }) {
  const [data, setData] = useState({ sponsor_name: '', sponsor_link: '', sponsor_logo: '' });

  useEffect(() => {
    fetch(`${apiRoot}wp/v2/event/${postId}`, {
      headers: { 'X-WP-Nonce': nonce }
    })
      .then(r => r.json())
      .then(post => {
        setData({
          sponsor_name: post.meta.sponsor_name || '',
          sponsor_link: post.meta.sponsor_link || '',
          sponsor_logo: post.meta.sponsor_logo || ''
        });
      });
  }, [postId]);

  const save = () => {
    fetch(`${apiRoot}wp/v2/event/${postId}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': nonce
      },
      body: JSON.stringify({ meta: data })
    });
  };

  return (
    <div className="ap-sponsored-config">
      <p>
        <label>{__('Sponsored By', 'artpulse')}
          <input type="text" value={data.sponsor_name} onChange={e => setData({ ...data, sponsor_name: e.target.value })} />
        </label>
      </p>
      <p>
        <label>{__('Sponsor Link', 'artpulse')}
          <input type="url" value={data.sponsor_link} onChange={e => setData({ ...data, sponsor_link: e.target.value })} />
        </label>
      </p>
      <p>
        <label>{__('Logo URL', 'artpulse')}
          <input type="text" value={data.sponsor_logo} onChange={e => setData({ ...data, sponsor_logo: e.target.value })} />
        </label>
      </p>
      <button type="button" onClick={save}>{__('Save Sponsor', 'artpulse')}</button>
    </div>
  );
}

export default function initSponsoredEventConfigWidget(el) {
  const root = createRoot(el);
  const { postId, apiRoot, nonce } = el.dataset;
  root.render(<SponsoredEventConfigWidget postId={postId} apiRoot={apiRoot} nonce={nonce} />);
}
