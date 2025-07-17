import React, { useEffect, useState } from 'react';

export default function ChatWidget({ eventId, canPost }) {
  const [messages, setMessages] = useState([]);
  const [text, setText] = useState('');

  const load = () => {
    fetch(`${APChat.apiRoot}artpulse/v1/event/${eventId}/chat`)
      .then(r => r.json())
      .then(setMessages);
  };

  useEffect(() => {
    load();
    const id = setInterval(load, 10000);
    return () => clearInterval(id);
  }, [eventId]);

  useEffect(() => {
    const list = document.querySelector('.ap-chat-list');
    if (list) list.scrollTop = list.scrollHeight;
  }, [messages]);

  const send = e => {
    e.preventDefault();
    const msg = text.trim();
    if (!msg) return;
    fetch(`${APChat.apiRoot}artpulse/v1/event/${eventId}/chat`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': APChat.nonce
      },
      body: JSON.stringify({ content: msg })
    }).then(() => {
      setText('');
      load();
    });
  };

  return (
    <div className="ap-event-chat" data-event-id={eventId}>
      <ul className="ap-chat-list" role="status" aria-live="polite">
        {messages.map(m => (
          <li key={m.id}>
            <img className="ap-chat-avatar" src={m.avatar} alt="" />
            <span className="ap-chat-author">{m.author}</span>
            <span className="ap-chat-time">{new Intl.DateTimeFormat('en', { timeStyle: 'short' }).format(new Date(m.created_at))}</span>
            <p className="ap-chat-content">{m.content}</p>
          </li>
        ))}
      </ul>
      {canPost ? (
        <form className="ap-chat-form" onSubmit={send}>
          <input
            type="text"
            aria-label="Chat message"
            value={text}
            onChange={e => setText(e.target.value)}
          />
          <button type="submit" aria-label="Send chat message">Send</button>
        </form>
      ) : (
        <p>{APChat.loggedIn ? 'Only attendees can post messages' : 'Please log in to chat.'}</p>
      )}
    </div>
  );
}
