import React, { useEffect, useState } from 'react';
const { __ } = wp.i18n;

export default function QaWidget({ eventId, canPost }) {
  const [comments, setComments] = useState([]);
  const [text, setText] = useState('');

  const load = () => {
    fetch(`${APQa.apiRoot}artpulse/v1/qa-thread/${eventId}`)
      .then(r => r.json())
      .then(d => setComments(d.comments || []));
  };

  useEffect(() => {
    load();
  }, [eventId]);

  const send = e => {
    e.preventDefault();
    const msg = text.trim();
    if (!msg) return;
    fetch(`${APQa.apiRoot}artpulse/v1/qa-thread/${eventId}/post`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': APQa.nonce
      },
      body: JSON.stringify({ content: msg })
    }).then(() => {
      setText('');
      load();
    });
  };

  return (
    <div className="ap-qa-thread" data-event-id={eventId}>
      <ul className="ap-qa-list">
        {comments.map(c => (
          <li key={c.id}>{c.author}: {c.content}</li>
        ))}
      </ul>
      {canPost && (
        <form className="ap-qa-form" onSubmit={send}>
          <textarea
            required
            aria-label={__('Your question', 'artpulse')}
            value={text}
            onChange={e => setText(e.target.value)}
          />
          <button type="submit" aria-label={__('Post question', 'artpulse')}>{__('Post', 'artpulse')}</button>
        </form>
      )}
    </div>
  );
}
