import React, { useEffect, useState } from 'react';
const { __ } = wp.i18n;

export default function ChatWidget({ eventId, canPost }) {
  const [messages, setMessages] = useState([]);
  const [text, setText] = useState('');
  const [error, setError] = useState(null);

  const load = async () => {
    try {
      const resp = await fetch(`${APChat.apiRoot}artpulse/v1/event/${eventId}/chat`, {
        headers: { 'X-WP-Nonce': APChat.nonce },
        credentials: 'same-origin'
      });
      const data = await resp.json();
      if (Array.isArray(data)) {
        setMessages(data);
      } else {
        setMessages([]);
      }
    } catch (err) {
      setError(err);
    }
  };

  useEffect(() => {
    load();
    let id = null;
    if (!window.IS_DASHBOARD_BUILDER_PREVIEW) {
      id = setInterval(load, 10000);
    }
    return () => { if (id) clearInterval(id); };
  }, [eventId]);

  useEffect(() => {
    const list = document.querySelector('.ap-chat-list');
    if (list) list.scrollTop = list.scrollHeight;
  }, [messages]);

  const send = async e => {
    e.preventDefault();
    const msg = text.trim();
    if (!msg) return;
    try {
      await fetch(`${APChat.apiRoot}artpulse/v1/event/${eventId}/chat`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': APChat.nonce
        },
        credentials: 'same-origin',
        body: JSON.stringify({ content: msg })
      });
      setText('');
      load();
    } catch (err) {
      setError(err);
    }
  };

  return (
    <div className="ap-event-chat" data-event-id={eventId}>
      <ul className="ap-chat-list" role="status" aria-live="polite">
        {Array.isArray(messages) && messages.length > 0 ? (
          messages.map(m => (
            <li key={m.id}>
              <img className="ap-chat-avatar" src={m.avatar} alt="" />
              <span className="ap-chat-author">{m.author}</span>
              <span className="ap-chat-time">{new Intl.DateTimeFormat('en', { timeStyle: 'short' }).format(new Date(m.created_at))}</span>
              <p className="ap-chat-content">{m.content}</p>
            </li>
          ))
        ) : (
          <li className="ap-chat-empty">{__('No messages yet.', 'artpulse')}</li>
        )}
      </ul>
      {error && (
        <p className="ap-chat-error">{__('Unable to load chat.', 'artpulse')}</p>
      )}
      {canPost ? (
        <form className="ap-chat-form" onSubmit={send}>
          <input
            type="text"
            aria-label={__('Chat message', 'artpulse')}
            value={text}
            onChange={e => setText(e.target.value)}
          />
          <button type="submit" aria-label={__('Send chat message', 'artpulse')}>{__('Send', 'artpulse')}</button>
        </form>
      ) : (
        <p>{APChat.loggedIn ? __('Only attendees can post messages', 'artpulse') : __('Please log in to chat.', 'artpulse')}</p>
      )}
    </div>
  );
}
